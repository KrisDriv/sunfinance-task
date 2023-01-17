<?php

namespace App;

use App\Http\Contracts\ResponsePresenter;
use Symfony\Component\HttpFoundation\Response;

class TestPresenter implements ResponsePresenter
{

    public function present(Response $response): void
    {
        echo 'Hello from ' . __CLASS__ . '::' . __METHOD__;
    }
}