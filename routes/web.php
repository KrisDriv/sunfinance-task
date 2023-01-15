<?php

declare(strict_types=1);

/** @var RouterInterface $router */

use App\Router\Contracts\RouterInterface;

$router->get('/test', 'TestController@index');
$router->get('/test/(\d+)', 'TestController@show');