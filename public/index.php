<?php
declare(strict_types=1);

use App\Contracts\Http\RequestHandler;
use App\Contracts\Http\ResponsePresenter;
use Symfony\Component\HttpFoundation\Request;

// Change directory to ROOT
chdir(dirname(__DIR__));

require 'bootstrap/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->getContainer()->get(ResponsePresenter::class)->present(
    $app->getContainer()->get(RequestHandler::class)->handle(
        $app->getContainer()->make(Request::class)
    )
);