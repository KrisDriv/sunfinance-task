<?php
declare(strict_types=1);

use Bramus\Router\Router;

/** @var Router $router */

$router->post('/payment', 'PaymentController::store');