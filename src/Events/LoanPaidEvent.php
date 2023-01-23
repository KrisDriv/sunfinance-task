<?php
declare(strict_types=1);

namespace App\Events;

use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;
use App\Entities\PaymentOrder;
use Symfony\Contracts\EventDispatcher\Event;

class LoanPaidEvent extends Event
{

    public const NAME = 'payment.loan.paid';

    public function __construct(protected PaymentEntity $payment,
                                protected LoanEntity    $loan,
                                protected ?PaymentOrder $refund,
    )
    {
    }

    public function getRefund(): ?PaymentOrder
    {
        return $this->refund;
    }

    public function getLoan(): LoanEntity
    {
        return $this->loan;
    }

    public function getPayment(): PaymentEntity
    {
        return $this->payment;
    }

}