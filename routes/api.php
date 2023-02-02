<?php
declare(strict_types=1);

use App\Router\Contracts\RouterInterface;

return function (RouterInterface $router): void {

    $router->get('/payment', 'Api\\PaymentController@create');
    $router->post('/payment', 'Api\\PaymentController@create');

};