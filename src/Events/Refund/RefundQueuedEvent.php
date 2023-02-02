<?php
declare(strict_types=1);

namespace App\Events\Refund;

use App\Entities\PaymentOrder;
use Symfony\Contracts\EventDispatcher\Event;

class RefundQueuedEvent extends Event
{

    public const NAME = 'refund.queued';

    public function __construct(protected PaymentOrder $paymentOrder)
    {
    }

    public function getPaymentOrder(): PaymentOrder
    {
        return $this->paymentOrder;
    }

}