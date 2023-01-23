<?php
declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Entities\PaymentEntity;
use Exception;
use Throwable;

class PaymentException extends Exception
{

    public function __construct(public PaymentEntity $paymentEntity,
                                string $message = "",
                                int $code = 0,
                                ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getPaymentEntity(): PaymentEntity
    {
        return $this->paymentEntity;
    }
}