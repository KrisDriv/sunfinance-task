<?php

namespace App\Events\Payment;

use App\Entities\PaymentEntity;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentFailedEvent extends Event
{

    public const NAME = 'payment.failed';

    public function __construct(protected PaymentEntity $paymentEntity)
    {
    }

    public function getPaymentEntity(): PaymentEntity
    {
        return $this->paymentEntity;
    }

}