<?php
declare(strict_types=1);

namespace App\Http\Contracts;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestHandler
{

    public function handle(Request $request): Response;

}