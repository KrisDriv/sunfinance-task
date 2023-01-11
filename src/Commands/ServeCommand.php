<?php
declare(strict_types=1);

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{

    protected static $defaultName = 'serve';
    protected static $defaultDescription = 'Start built-in php web server for development';

    protected function configure()
    {
        $this->addArgument('address', InputArgument::OPTIONAL, 'Local server address', 'localhost');
        $this->addArgument('port', InputArgument::OPTIONAL, 'Local server port', '80');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = 0;

        $address = $input->getArgument('address');
        $port = $input->getArgument('port');

        exec("php -S $address:$port public/serve.php", result_code: $code);

        return $code;
    }

}