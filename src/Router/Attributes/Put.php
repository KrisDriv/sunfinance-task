<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Symfony\Component\HttpFoundation\Request;

class Put extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath, Request::METHOD_PUT);
    }
}