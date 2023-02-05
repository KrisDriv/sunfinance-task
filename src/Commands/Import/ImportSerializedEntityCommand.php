<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Application;
use App\Commands\Traits\EntityImportHooks;
use App\Config\EntityImportConfig;
use App\Exceptions\Import\ImportException;
use App\Services\CsvFileReaderService;
use App\Services\EntityHydrateService;
use App\Services\JsonFileReaderService;
use App\Tables\AbstractTable;
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
    use EntityImportHooks;

    public function __construct(protected readonly Application         $application,
                                private readonly JsonFileReaderService $jsonFileReaderService,
                                private readonly CsvFileReaderService  $csvFileReaderService,
                                private readonly EntityHydrateService  $entityHydrateService,
                                private readonly EntityImportConfig    $importConfig)
    {
        parent::__construct();
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
            'force',
            'f',
            InputOption::VALUE_NEGATABLE,
            'Skip over exceptions, and continue with next row',
            true
        );

        $this->addOption(
            'format',
            't',
            InputOption::VALUE_OPTIONAL,
            'Provide file format to expect. If not provided will try to automatically detect',
            'auto'
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
        $format = $input->getOption('format');

        $detectedFormat = pathinfo($file, PATHINFO_EXTENSION);

        try {
            $fileContents = match ($usedFormat = ($format === 'auto' ? $detectedFormat : $format)) {

                'csv' => $this->csvFileReaderService->readFile($file),
                'json' => $this->jsonFileReaderService->readFile($file),

                default => throw new Exception("Unsupported file format: '$usedFormat'")

            };
        } catch (Exception $e) {
            $output->error('Unable to read file: ' . $e->getMessage());

            return Command::FAILURE;
        }

        /** @var [EntityException, array] $errors */
        $errors = [];

        /** @var AbstractTable $table */
        $imported = 0;
        try {
            $table = $this->application->getContainer()->get(static::ENTITY_TABLE);

            foreach ($fileContents as $index => $row) {
                $listItemPrefix = "  " . ($index + 1) . ". {$this->identifier($row)}";

                // Attempting to hydrate
                try {
                    $row = $this->entityHydrateService->translateKeys(
                        $row,
                        $this->importConfig->getKeyTranslationArrayFor(static::ENTITY_CLASS)
                    );

                    if (!is_null($exitCode = $this->preHydrate($row, $output)) && $force) {
                        return $exitCode;
                    }

                    $entity = $this->entityHydrateService->fromArray($row, static::ENTITY_CLASS);
                } catch (Exception $e) {
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

                        if (!is_null($exitCode = $this->onDuplicate($entity, $output)) && !$force) {
                            return $exitCode;
                        }
                    } else {
                        if (!is_null($exitCode = $this->preSave($entity, $output)) && $force) {
                            return $exitCode;
                        }

                        if ($entity->isSaved()) {
                            $output->writeln("<fg=green>$listItemPrefix: Processed</fg=green>");
                        } else {
                            $table->save($entity);

                            $output->writeln("<fg=green>$listItemPrefix: Imported</fg=green>");
                        }

                        $imported++;
                    }
                } catch (Throwable $e) {
                    $errors[] = [EntityException::fromThrowable($e), $row];
                    $output->writeln("<error>$listItemPrefix: Failed while processing</error>");

                    if ($force) {
                        continue;
                    } else {
                        if ($e instanceof ImportException) {
                            return $e->getCode();
                        }
                    }

                    break;
                }
            }
        } catch (Throwable $e) {
            $output->error(get_class($e) . ": " . $e->getMessage());

            return Command::FAILURE;
        }

        // Print collected exceptions
        if ($output->isVerbose()) {
            foreach ($errors as $i => $errorData) {
                [$entityException, $row] = $errorData;

                $output->error(
                    ("#" . ($i + 1) . ": [")
                    . get_class($entityException) . "]: "
                    . ($entityException->getPrevious() ?: $entityException)->getMessage()
                );

                $output->block(json_encode($row, JSON_PRETTY_PRINT));
            }
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
            if ($output->isVerbose()) {
                $output->comment('Failed to import '
                    . ($c = count($errors))
                    . ' '
                    . ($c > 1 ? 'entities' : 'entity')
                    . ', see exceptions above.'
                );
            }

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