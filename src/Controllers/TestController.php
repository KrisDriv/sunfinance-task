<?php

namespace App\Controllers;

class TestController
{

    #[App\Router\Attributes\Get('/hello-world')]
    public function index(): void
    {
        echo 'Hello there!';
    }

}