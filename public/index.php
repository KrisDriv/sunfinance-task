<?php
declare(strict_types=1);

chdir(dirname(__DIR__));

use Bramus\Router\Router;

/**
 * Bootstrap the Application
 */
require 'bootstrap.php';

/** @var Router $router */
$response = $router->run();