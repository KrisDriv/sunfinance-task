<?php
declare(strict_types=1);

namespace App\Commands\Bases;

use App\Application;
use App\Services\EntityHydrateService;
use App\Services\JsonFileReaderService;
use App\Tables\AbstractTable;
use Composite\Entity\AbstractEntity;
use Composite\Entity\Exceptions\EntityException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

abstract class ImportSerializedEntityCommand extends Command
{

    public function __construct(protected readonly Application         $application,
                                private readonly JsonFileReaderService $jsonFileReaderService,
                                private readonly EntityHydrateService  $entityHydrateService)
    {
        parent::__construct();
    }

    /**
     * Do extra work on entity before persisting. Throw EntityException to skip this entity and display the error message
     *
     * @param AbstractEntity $entity
     * @return void
     */
    protected function preprocessEntity(AbstractEntity $entity): void
    {

    }

    /**
     * @throws Exception
     */
    public function configure()
    {
        if (static::ENTITY_CLASS === null || static::ENTITY_TABLE === null) {
            throw new Exception('Sub-class of ' . self::class . ' must define ENTITY_CLASS and ENTITY_TABLE constants');
        }

        $this->addOption(
            '--force',
            'f',
            InputOption::VALUE_NEGATABLE,
            'Skip over exceptions, and continue with next row',
            true
        );

        $this->addArgument(
            'file',
            InputArgument::REQUIRED,
            'file from which to read JSON data'
        );
    }

    public function execute(InputInterface $input, SymfonyStyle|OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $force = $input->getOption('force');

        /** @var [EntityException, array] $errors */
        $errors = [];

        /** @var AbstractTable $table */
        $imported = 0;
        try {
            $table = $this->application->getContainer()->get(static::ENTITY_TABLE);

            foreach ($this->jsonFileReaderService->readJsonFile($file) as $index => $row) {
                $listItemPrefix = "  " . ($index + 1) . ". {$this->identifier($row)}";

                // Attempting to hydrate
                try {
                    $entity = $this->entityHydrateService->fromArray($row, static::ENTITY_CLASS, static::KEY_TRANSLATIONS ?? null);

                    $this->preprocessEntity($entity);
                } catch (EntityException $e) {
                    $errors[] = [$e, $row];
                    $output->writeln("<error>$listItemPrefix: Failed to hydrate</error>");

                    if ($force) {
                        continue;
                    }

                    break;
                }

                // Performing the save
                try {
                    if ($table->exists($entity)) {
                        $output->writeln("<comment>$listItemPrefix: Duplicate</comment>");
                    } else {
                        $table->save($entity);

                        $output->writeln("<green>$listItemPrefix: Imported</green>");
                        $imported++;
                    }
                } catch (Throwable $e) {
                    $errors[] = [EntityException::fromThrowable($e), $row];
                    $output->writeln($listItemPrefix . ': Failed to save');

                    if ($force) {
                        continue;
                    }

                    break;
                }
            }
        } catch (Throwable $e) {
            $output->error(get_class($e) . ": " . $e->getMessage());

            return Command::FAILURE;
        }

        // Print collected exceptions
        foreach ($errors as $errorData) {
            [$entityException, $row] = $errorData;

            $output->error(
                get_class($entityException) . ": " . ($entityException->getPrevious() ?: $entityException)
                    ->getMessage()
            );

            $output->note('Attempted serialized content:');
            $output->block(json_encode($row, JSON_PRETTY_PRINT));
        }

        // Print result footer
        if ($imported > 0) {
            $output->success('Successfully imported '
                . $imported
                . ' '
                . ($imported > 1 ? 'entities' : 'entity')
                . '!'
            );
        } elseif (empty($errors)) {
            $output->warning('No entities were imported.');
        } else {
            $output->comment('Failed to import '
                . ($c = count($errors))
                . ' '
                . ($c > 1 ? 'entities' : 'entity')
                . ', see exceptions above.'
            );

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function displayableColumns(array $row): ?array
    {
        $eligibleRows = array_filter(
            $row,
            function (mixed $column): bool {
                return is_string($column) && strlen($column) < 31;
            }
        );

        return array_chunk($eligibleRows, 3)[0] ?? null;
    }

    private function identifier(array $row): string
    {
        if (is_null($columns = $this->displayableColumns($row))) {
            return 'UNKNOWN';
        }

        return implode(' ', $columns);
    }

}