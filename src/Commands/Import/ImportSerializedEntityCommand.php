<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Application;
use App\Config\EntityImportConfig;
use App\Import\ImportConfiguration;
use App\Services\CsvFileReaderService;
use App\Services\EntityHydrateService;
use App\Services\JsonFileReaderService;
use Exception;
use Psr\Log\LoggerInterface;
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
                                private readonly CsvFileReaderService  $csvFileReaderService,
                                private readonly EntityHydrateService  $entityHydrateService,
                                private readonly EntityImportConfig    $importConfig,
                                private readonly LoggerInterface       $logger)
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
            'deep',
            'd',
            InputOption::VALUE_NEGATABLE,
            'Trigger hooks on import layers',
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
        $deep = $input->getOption('deep');
        $format = $input->getOption('format');

        $output->info('Hello, world!');
        $this->logger->info('Importing entities from a file...');

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


        try {
            $table = $this->application->getContainer()->get(static::ENTITY_TABLE);

            $importConfiguration = new ImportConfiguration(
                rows: $fileContents,
                entityClass: static::ENTITY_CLASS,
                table: $table,
                layers: $this->importConfig->getLayersFor(static::ENTITY_CLASS)
            );

            if (count($importConfiguration->layers) !== count(EntityImportConfig::LAYERS[static::ENTITY_CLASS] ?? []) && $deep) {
                $output->error('Missing layers, with deep option enabled import can not continue. Check logs for more information');
            }

            try {
                $this->entityHydrateService->import($importConfiguration);
            } catch (Throwable $e) {
                $output->error($e->getMessage());
            }

        } catch (Exception $e) {
            $output->error('Unable to read file: ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}