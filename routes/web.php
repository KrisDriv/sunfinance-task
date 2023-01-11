<?php

declare(strict_types=1);

use Bramus\Router\Router;

/** @var Router $router */

$router->get('/test', 'TestController@index');