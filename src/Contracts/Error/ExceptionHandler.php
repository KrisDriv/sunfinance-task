<?php
declare(strict_types=1);

namespace App\Contracts\Error;

use Exception;

interface ExceptionHandler
{

    public function handleException(Exception $e): void;

}