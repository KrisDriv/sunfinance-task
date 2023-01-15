<?php
declare(strict_types=1);

/** @var Router $router */

use App\Router\Router;

$router->post('/payment', 'PaymentController::store');