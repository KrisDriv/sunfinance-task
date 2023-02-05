<?php
declare(strict_types=1);

namespace App\Exceptions\Import;

use App\Exceptions\Exception;
use Composite\Entity\AbstractEntity;
use Throwable;

/**
 * Import exceptions hold exit codes which will be used to exit console application
 */
class ImportException extends Exception
{

    public function __construct(public array|AbstractEntity $entity,
                                string                      $message = "",
                                int                         $code = 0,
                                ?Throwable                  $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

}