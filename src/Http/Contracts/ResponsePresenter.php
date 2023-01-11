<?php
declare(strict_types=1);

namespace App\Http\Contracts;

use Symfony\Component\HttpFoundation\Response;

interface ResponsePresenter
{

    public function present(Response $response): string;

}