<?php

namespace App\Commands\Route;

use App\Router\Contracts\RouterInterface;
use Closure;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'route:list',
    description: 'Lists registered routes',
    aliases: ['routes'],
    hidden: false
)]
class RouteListCommand extends Command
{

    public function __construct(private RouterInterface $router)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, SymfonyStyle|OutputInterface $output)
    {
        $allRoutes = $this->router->getAllRoutes();

        $displayRows = [];
        foreach ($allRoutes as $method => $routes) {
            foreach ($routes as $route) {
                [$pattern, $handler] = array_values($route);

                $displayRows[] = [strtoupper($method), $pattern, is_string($handler) ? $handler : $this->getCallableName($handler)];
            }
        }

        $output->table(['Method', 'Pattern', 'Handler'], $displayRows);

        return Command::SUCCESS;
    }

    private function getCallableName(callable|object $callable): string
    {
        return match (true) {
            is_string($callable) && strpos($callable, '::') => '[static] ' . $callable,
            is_string($callable) => '[function] ' . $callable,
            is_array($callable) && is_object($callable[0]) => '[method] ' . get_class($callable[0]) . '->' . $callable[1],
            is_array($callable) => '[static] ' . $callable[0] . '::' . $callable[1],
            $callable instanceof Closure => '[closure]',
            is_object($callable) => '[invokable] ' . get_class($callable),
            default => '[unknown]',
        };
    }

}