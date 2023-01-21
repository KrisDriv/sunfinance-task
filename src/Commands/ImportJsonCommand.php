<?php
declare(strict_types=1);

namespace App\Commands;

use App\Application;
use App\Entities\CustomerEntity;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Composite\DB\Attributes\Column;
use Composite\Entity\AbstractEntity;
use DateTimeImmutable;
use DateTimeInterface;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use InvalidArgumentException;
use JsonException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\s;

abstract class ImportJsonCommand extends Command
{

    public function __construct(private readonly Application $application)
    {
        parent::__construct();
    }

    public function configure()
    {
        $this->addArgument('file', InputArgument::REQUIRED, 'file from which to read JSON data');
    }

    /**
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws DependencyException
     * @throws JsonException
     */
    public function execute(InputInterface $input, SymfonyStyle|OutputInterface $output): int
    {
        if (static::ENTITY_CLASS === null || static::ENTITY_TABLE === null) {
            $output->error('Sub-class of ' . self::class . ' must define ENTITY_CLASS and ENTITY_TABLE constants');

            return Command::FAILURE;
        }

        $file = $input->getArgument('file');
        $table = $this->application->getContainer()->get(static::ENTITY_TABLE);

        foreach ($this->readJsonFile($file) as $row) {
            $constructorArguments = $this->transformRowDataAccordingToEntityMetadata(
                $this->translateRowKeys($row, static::KEY_MAPPING ?? []),
                static::ENTITY_CLASS
            );

            $entityObject = new CustomerEntity(...array_values($constructorArguments));

            $table->save($entityObject);

            $output->writeln(" * $entityObject->first_name $entityObject->last_name imported");
        }

        return Command::SUCCESS;
    }

    /**
     * Reads and decodes JSON file
     *
     * @return array associated
     * @throws JsonException|InvalidArgumentException
     */
    protected function readJsonFile(string $relativeFilePath): array
    {
        $absoluteFilePath = base_path($relativeFilePath);

        if (!file_exists($absoluteFilePath)) {
            throw new InvalidArgumentException("File '$absoluteFilePath' does not exist");
        }

        return json_decode(
            file_get_contents($absoluteFilePath),
            true,
            flags: JSON_THROW_ON_ERROR
        );
    }

    /**
     * Changes array keys to those in key translation array. It is not case-sensitive
     * to keys in target array.
     *
     * @param array $row
     * @param array $keyTranslations
     * @return array
     */
    protected function translateRowKeys(array $row, array $keyTranslations): array
    {
        foreach ($row as $key => $data) {
            if (!array_key_exists($lowercaseKey = strtolower($key), $keyTranslations)) {
                continue;
            }

            $row[$keyTranslations[$lowercaseKey]] = $data;
            unset($row[$key]);
        }

        return $row;
    }

    /**
     * Reads entity class property data and using that information validates/transforms given raw array.
     * Will throw an exception if any data point fails to convert or is missing. Does respect optional/nullable
     * properties.
     *
     * @param array $row
     * @param string|AbstractEntity $entity
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function transformRowDataAccordingToEntityMetadata(array $row, string|AbstractEntity $entity): array
    {
        if (!is_subclass_of($entity, AbstractEntity::class)) {
            throw new InvalidArgumentException('Passed $entityClass is not extending ' . AbstractEntity::class . ' class');
        }

        $reflectiveEntityClass = new ReflectionClass($entity);
        $constructorMethod = $reflectiveEntityClass->getMethod('__construct');
        $constructorParameters = [];

        foreach ($constructorMethod->getParameters() as $parameter) {
            if (!$parameter->isPromoted()) {
                continue;
            }

            $constructorParameters[$parameter->getName()] = $parameter;
        }

        $validProperties = [];
        foreach ($reflectiveEntityClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            // Skip these, we can't write these properties anyway
            if ($reflectionProperty->isReadOnly()) {
                continue;
            }

            $snakeCasedName = s($reflectionProperty->getName())->snake()->lower()->toString();

            $propertyType = $reflectionProperty->getType();

            // see: https://bugs.php.net/bug.php?id=81386&edit=1
            // we can not know if promoted parameters have default value by looking at class properties
            if ($promotedParameter = $constructorParameters[$reflectionProperty->getName()] ?? null) {
                $parameterType = $promotedParameter->getType();

                $isPropertyOptional = $parameterType !== null
                    && ($parameterType->allowsNull() || $promotedParameter->isDefaultValueAvailable());
            } else {
                $isPropertyOptional = $propertyType !== null
                    && ($reflectionProperty->getType()->allowsNull() || $reflectionProperty->hasDefaultValue());
            }

            $isPropertyGiven = isset($row[$snakeCasedName]);

            if ($isPropertyGiven === false) {
                if ($isPropertyOptional === false) {
                    throw new Exception("Property '$snakeCasedName' is required but missing");
                } else {
                    $row[$snakeCasedName] = null;
                }
            }

            try {
                $validProperties[$reflectionProperty->getName()] = $this->resolveEntityParameter($row[$snakeCasedName] ?? null, $reflectionProperty);
            } catch (Exception $e) {
                throw new Exception("$snakeCasedName: " . $e->getMessage(), previous: $e);
            }
        }

        return $validProperties;
    }

    /**
     * @throws Exception
     */
    private function resolveEntityParameter($raw, ReflectionProperty|ReflectionParameter $property): mixed
    {
        $resolved = $raw;
        $propertyType = $property->getType();
        $argumentType = gettype($raw);

        // gettype and ReflectionType::getName() mismatch
        $argumentType = match ($argumentType) {
            'boolean' => 'bool',
            'integer' => 'int',
            default => $argumentType
        };

        if ($propertyType instanceof ReflectionIntersectionType) {
            throw new Exception('Intersection types are not supported');
        }

        if ($propertyType instanceof ReflectionUnionType) {
            $matchedType = null;
            foreach ($propertyType->getTypes() as $type) {
                // We skip here, cause for these we will have validation below
                if ($type->isBuiltin() === false) {
                    continue;
                }

                if (gettype($raw) === $type->getName()) {
                    $matchedType = $type;
                    break;
                }
            }

            if (!$matchedType) {
                throw new Exception('Invalid type, expected one of following types: '
                    . implode(', ', array_map(
                        fn(ReflectionNamedType $namedType): string => $namedType->getName(),
                        $propertyType->getTypes()
                    ))
                    . ". Got $argumentType instead."
                );
            } else {
                $propertyType = $matchedType;
            }

        }

        // Covers both conditions, code above will select appropriate type and continue here.
        // However, we must type check here again in case it wasn't union type and was never typed checked
        if ($propertyType instanceof ReflectionNamedType) {
            if ($propertyType->isBuiltin()) {
                $isTypeMismatch = $argumentType !== $propertyType->getName();
                // The type might not be matching, but NULL can still be possible value
                if ($isTypeMismatch && ($propertyType->allowsNull() === false || $raw !== null)) {
                    throw new Exception("Invalid type, expected {$propertyType->getName()}. Got $argumentType instead.");
                }

                settype($raw, $propertyType->getName());
            }

            // If type-hinted non-built-in class. We must try and convert given value to that type if possible
            if (is_subclass_of($propertyType->getName(), DateTimeInterface::class)) {
                if ($propertyType->getName() === DateTimeImmutable::class || is_subclass_of($propertyType->getName(), DateTimeImmutable::class)) {
                    return CarbonImmutable::parse($raw);
                }

                return Carbon::parse($raw);
            }

            if (enum_exists($propertyType->getName())) {
                // Some enums may not be possible to resolve, lets see if resolving is actually necessary
                if ($propertyType->allowsNull()) {
                    return null;
                } else {
                    throw new Exception("Enum {$propertyType->getName()} can not be resolved");
                }
            }
        }

        $attributes = $property->getAttributes(Column::class, ReflectionAttribute::IS_INSTANCEOF);

        if (!empty($attribute)) {
            foreach ($attributes as $attribute) {
                // TODO: Implement attribute constraints
            }
        }

        return $resolved;
    }

}