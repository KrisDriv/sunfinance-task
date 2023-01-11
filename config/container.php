<?php

use App\Router\Contracts\RouterInterface;
use App\Router\Router;
use Composite\DB\ConnectionManager;
use Doctrine\DBAL\Connection;
use HaydenPierce\ClassFinder\ClassFinder;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use function DI\create;

$discover = [
    'App\\Commands',
    'App\\Services',
    'App\\Controllers'
];

$definitions = [];
foreach ($discover as $namespace) {
    try {
        $classes = ClassFinder::getClassesInNamespace($namespace);

        foreach ($classes as $class) {
            $discover[$class] = create($class);
        }
    } catch (Exception $e) {
        // TODO: Log
        continue;
    }
}

return array_merge($definitions, [

    Request::class => function() {
        return \Illuminate\Http\Request::capture();
    },
    RouterInterface::class => create(Router::class),
    Connection::class => function () {
        return ConnectionManager::getConnection(env('DATABASE_CONNECTION'));
    }
]);