<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(
        public string       $routePath,
        public array|string $methods = Request::METHOD_GET
    )
    {
    }
}