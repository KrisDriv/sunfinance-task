<?php
declare(strict_types=1);

use App\Application;
use App\Contracts\Error\ExceptionHandler;
use App\ErrorHandler;
use App\Router\Contracts\RouterInterface;
use App\Router\Router;
use Carbon\Carbon;
use Composite\DB\ConnectionManager;
use Doctrine\DBAL\Connection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    LoggerInterface::class => function () {
        $date = Carbon::now()->format('d-m-Y');

        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(tmp_path("$date.log")));
        $logger->pushHandler(new ConsoleHandler());

        return $logger;
    },

    EventDispatcher::class => function () {
        return new EventDispatcher();
    },

    Application::class => function () {
        global $app;

        return $app;
    },

    \App\Contracts\Error\ErrorHandler::class => $errorHandlerCallback = function () {
        static $handler;

        if ($handler) {
            return $handler;
        }

        $handler = new ErrorHandler();

        return $handler;
    },

    ExceptionHandler::class => $errorHandlerCallback
];