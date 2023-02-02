<?php
declare(strict_types=1);

namespace App\Contracts\Error;

interface ErrorHandler
{

    public function handleError(int $errorCode, string $errorString, string $errorFile, int $errorLine): void;

}