<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Get extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath);
    }
}