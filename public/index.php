<?php
declare(strict_types=1);

use App\Application;
use Symfony\Component\HttpFoundation\Request;

chdir(dirname(__DIR__));

/**
 * Bootstrap the Application
 */
require_once 'bootstrap.php';

/** @var Application $app */

$app->present(
    $app->handle($app->getContainer()->make(Request::class))
);