<?php
declare(strict_types=1);

namespace App\Contracts\Http;

use Symfony\Component\HttpFoundation\Response;

interface ResponsePresenter
{

    public function present(Response $response): void;

}