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
        $address = $input->getArgument('address');
        $port = $input->getArgument('port');

        // TODO: Does this open up a vulnerability or since this is never suppose to be available
        //  to the end user it's not a concern?
        exec("php -S $address:$port public/serve.php", result_code: $code);

        return $code;
    }

}