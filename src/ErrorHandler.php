<?php
declare(strict_types=1);

namespace App;

use Error;
use ErrorException;
use Exception;

/**
 * Simple error-handler mapping PHP errors to PHP's ErrorException, and registering
 * a global exception-handler that slightly improves Exception display.
 *
 * @see ErrorException
 */
class ErrorHandler implements Contracts\Error\ExceptionHandler, Contracts\Error\ErrorHandler
{

    /**
     * Register error-handler and Exception-handler.
     */
    public function __construct()
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
    }

    /**
     * @throws ErrorException
     */
    public function handleError(int $errorCode, string $errorString, string $errorFile, int $errorLine): void
    {
        if (error_reporting() === 0) {
            return; // error-suppression operator was used - do not throw.
        }

        throw new ErrorException($errorString, 0, $errorCode, $errorFile, $errorLine);
    }

    public function handleException(Exception|Error $e): void
    {
        $html = true;

        foreach (headers_list() as $header) {
            if ((stripos($header, 'content-type:') === 0) && (stripos($header, 'html') === false)) {
                $html = false; // a non-html content-type has been set
            }
        }

        echo $html
            ? '<pre>' . htmlspecialchars($e->__toString()) . '</pre>'
            : $e->__toString();
    }
}