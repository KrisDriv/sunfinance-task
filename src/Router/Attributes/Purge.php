<?php
declare(strict_types=1);

namespace App\Router\Attributes;

use Symfony\Component\HttpFoundation\Request;

class Purge extends Route
{
    public function __construct(string $routePath)
    {
        parent::__construct($routePath, Request::METHOD_PURGE);
    }
}