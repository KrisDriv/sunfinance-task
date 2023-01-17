<?php

declare(strict_types=1);

use App\Router\Contracts\RouterInterface;

return function (RouterInterface $router): void {

    $router->get('/test', fn() => 'Hello, world!');

};