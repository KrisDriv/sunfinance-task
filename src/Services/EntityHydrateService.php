<?php

namespace App\Services;

use App\Exceptions\Entity\EntityException;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Composite\Entity\AbstractEntity;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Throwable;
use function Symfony\Component\String\s;

/**
 * This services gives more control of entity hydration.
 *
 * Was this necessary? No! It was just fun to write.
 *
 * @see AbstractEntity::fromArray
 */
class EntityHydrateService
{

    /**
     * Changes array keys to those in key translation array. It is not case-sensitive
     * to keys in target array.
     *
     * @param array $row
     * @param array $keyTranslations
     * @return array
     */
    public function translateKeys(array $row, array $keyTranslations): array
    {
        $snakeCasedKeys = [];
        foreach (array_keys($row) as $key) {
            if (!($snakeCasedKey = s($key)->snake()->lower())->equalsTo($key)) {
                $snakeCasedKeys[strtolower($key)] = $snakeCasedKey->toString();
            }
        }

        $keyTranslations = array_merge($snakeCasedKeys, $keyTranslations);

        foreach ($row as $key => $data) {
            if (!array_key_exists($lowercaseKey = strtolower($key), $keyTranslations)) {
                continue;
            }

            if (($translatedKey = $keyTranslations[$lowercaseKey]) !== $lowercaseKey) {
                $row[$translatedKey] = $data;
                unset($row[$key]);
            }
        }

        return $row;
    }

    /**
     * @param array $data
     * @param string $entityClass
     * @param array|null $keyTranslations
     *
     * @return AbstractEntity
     *
     * @throws EntityException
     */
    public function fromArray(array $data, string $entityClass, ?array $keyTranslations = null): AbstractEntity
    {
        if ($keyTranslations !== null) {
            $data = $this->translateKeys($data, $keyTranslations);
        }

        try {
            $constructorArguments = $this->resolveEntityConstructor($data, $entityClass);
            $writableProperties = $this->resolveEntityProperties($data, $entityClass);
        } catch (Throwable $throwable) {
            throw EntityException::fromThrowable($throwable);
        }

        $entity = new ($entityClass)(...array_values($constructorArguments));

        foreach ($writableProperties as $propertyKey => $propertyValue) {
            $entity->{$propertyKey} = $propertyValue;
        }

        return $entity;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function resolveEntityProperties(array $row, string|AbstractEntity $entity): array
    {
        if (!is_subclass_of($entity, AbstractEntity::class)) {
            throw new InvalidArgumentException('Passed $entity is not extending ' . AbstractEntity::class . ' class');
        }

        $reflectiveEntityClass = new ReflectionClass($entity);
        $validProperties = [];

        foreach ($reflectiveEntityClass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            if ($reflectionProperty->isPromoted() || $reflectionProperty->isReadOnly()) {
                continue;
            }

            $snakeCasedName = s($reflectionProperty->getName())->snake()->lower()->toString();

            if (!isset($row[$snakeCasedName])) {
                continue;
            }

            try {
                $validProperties[$reflectionProperty->getName()] = $this->castProperty($row[$snakeCasedName] ?? null, $reflectionProperty);
            } catch (Exception $e) {
                throw new Exception("$snakeCasedName: " . $e->getMessage(), previous: $e);
            }
        }

        return $validProperties;
    }

    /**
     * Reads entity class constructor signature and attempts to cast given raw data accordingly.
     * Will throw an exception if any data point fails to convert or is missing. Does respect optional/nullable
     * properties.
     *
     * @param array $row
     * @param string|AbstractEntity $entity
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function resolveEntityConstructor(array $row, string|AbstractEntity $entity): array
    {
        if (!is_subclass_of($entity, AbstractEntity::class)) {
            throw new InvalidArgumentException('Passed $entityClass is not extending ' . AbstractEntity::class . ' class');
        }

        $reflectiveEntityClass = new ReflectionClass($entity);
        $constructorMethod = $reflectiveEntityClass->getMethod('__construct');

        $validProperties = [];

        foreach ($constructorMethod->getParameters() as $reflectionParameter) {
            $snakeCasedName = s($reflectionParameter->getName())->snake()->lower()->toString();

            $parameterType = $reflectionParameter->getType();

            $isPropertyOptional = $parameterType !== null
                && ($parameterType->allowsNull() || $reflectionParameter->isDefaultValueAvailable());

            $isPropertyGiven = isset($row[$snakeCasedName]);

            if ($isPropertyGiven === false) {
                if ($isPropertyOptional === false) {
                    throw new Exception("Property '$snakeCasedName' is required but missing");
                } else {
                    $row[$snakeCasedName] = null;
                }
            }

            try {
                $validProperties[$reflectionParameter->getName()] = $this->castProperty($row[$snakeCasedName] ?? null, $reflectionParameter);
            } catch (Exception $e) {
                throw new Exception("$snakeCasedName: " . $e->getMessage(), previous: $e);
            }
        }

        return $validProperties;
    }

    /**
     * Attempts to cast given raw value to appropriate data type.
     *
     * Intersection type-hinted properties are not supported, and arrays will throw an exception as well
     *
     * @throws Exception
     */
    private function castProperty($raw, ReflectionProperty|ReflectionParameter $property): mixed
    {
        $resolved = $raw;
        $propertyType = $property->getType();

        $argumentType = get_reflection_aligned_type($raw);

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
            if ($propertyType->allowsNull() && $raw === null) {
                return null;
            }

            if ($propertyType->isBuiltin()) {
                // Attempts to cast to appropriate data type or returns original to trigger exception
                $casted = match ($propertyType->getName()) {

                    'float' => is_numeric($raw)
                        ? (float)$raw
                        : $raw,

                    'int' => is_numeric($raw)
                        ? (int)$raw
                        : $raw,

                    'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                        ? (bool)$raw
                        : $raw,

                    'string' => !is_string($raw)
                        ?: (string)$raw,

                    'is_array' => throw new Exception('Arrays are not supported')
                };

                $isTypeMismatch = $argumentType !== $propertyType->getName()
                    && ($argumentType = get_reflection_aligned_type($casted)) !== $propertyType->getName();

                // The type might not be matching, but NULL can still be possible value
                if ($isTypeMismatch) {
                    throw new Exception("Invalid type, expected {$propertyType->getName()}. Got $argumentType instead.");
                }

                return $casted;
            }

            // If type-hinted non-built-in class. We must try and convert given value to that type if possible
            if (is_subclass_of($propertyType->getName(), DateTimeInterface::class)) {
                if ($propertyType->getName() === DateTimeImmutable::class || is_subclass_of($propertyType->getName(), DateTimeImmutable::class)) {
                    return CarbonImmutable::parse($raw);
                }

                return Carbon::parse($raw);
            }

            if (enum_exists($propertyType->getName())) {
                return constant($propertyType->getName() . '::' . strtoupper($raw));
            }
        }

        return $resolved;
    }

}