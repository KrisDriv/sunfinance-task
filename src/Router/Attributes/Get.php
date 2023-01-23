<?php
declare(strict_types=1);

namespace App\Router\Attributes;

class Get extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath);
    }
}