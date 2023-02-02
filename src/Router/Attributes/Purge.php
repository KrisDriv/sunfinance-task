<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Attribute;
use Symfony\Component\HttpFoundation\Request;

#[Attribute(Attribute::TARGET_METHOD)]
class Purge extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath, Request::METHOD_PURGE);
    }
}