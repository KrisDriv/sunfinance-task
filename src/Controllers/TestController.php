<?php

namespace App\Controllers;

use App\Router\Contracts\RouterInterface;
use Illuminate\Http\Request;

class TestController
{

    public function index(Request $request, RouterInterface $router): array
    {
        return $router->getAllRoutes();
    }

}