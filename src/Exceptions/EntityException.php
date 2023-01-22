<?php
declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

class EntityException extends \Composite\Entity\Exceptions\EntityException
{

    public function __construct(string $message = "", ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }

    public static function fromThrowable(Throwable $throwable): self
    {
        return new self($throwable->getMessage(), $throwable);
    }

}