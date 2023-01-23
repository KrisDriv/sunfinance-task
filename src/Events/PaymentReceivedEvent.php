<?php
declare(strict_types=1);

namespace App\Events;

use App\Entities\PaymentEntity;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentReceivedEvent extends Event
{

    public const NAME = 'payment.received';

    public function __construct(protected PaymentEntity $paymentEntity)
    {
    }

    public function getPaymentEntity(): PaymentEntity
    {
        return $this->paymentEntity;
    }

}