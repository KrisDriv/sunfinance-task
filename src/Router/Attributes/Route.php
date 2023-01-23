<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Symfony\Component\HttpFoundation\Request;

class Route
{
    public function __construct(
        public string       $routePath,
        public array|string $methods = Request::METHOD_GET
    )
    {
    }
}