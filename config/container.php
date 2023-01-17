<?php
declare(strict_types=1);

use App\Router\Contracts\RouterInterface;
use App\Router\Router;
use Composite\DB\ConnectionManager;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use function DI\create;

return [
    Request::class => function () {
        return \Illuminate\Http\Request::capture();
    },
    RouterInterface::class => create(Router::class),
    Connection::class => function () {
        return ConnectionManager::getConnection(env('DATABASE_CONNECTION'));
    },
];