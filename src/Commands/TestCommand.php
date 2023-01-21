<?php

namespace App\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test',
    description: 'Launch PHPUnit tests',
    aliases: ['test'],
    hidden: false
)]
class TestCommand extends Command
{

    public function configure()
    {
        $this->addArgument('folder|file', InputArgument::OPTIONAL, 'Directory or file', 'tests');
    }

    public function execute(InputInterface $input, SymfonyStyle|OutputInterface $output)
    {
        $absolutePhpUnitExecutablePath = base_path('vendor/bin/phpunit');

        if (!file_exists($absolutePhpUnitExecutablePath)) {
            $output->error("Missing phpunit executable. Try running 'composer install'");

            return Command::FAILURE;
        }

        $folderOrFile = base_path($input->getArgument('folder|file'));

        exec("$absolutePhpUnitExecutablePath $folderOrFile", $executedCommandOutput, result_code: $code);

        foreach ($executedCommandOutput as $line) {
            $output->writeln($line);
        }

        return $code;
    }

}