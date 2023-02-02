<?php
declare(strict_types=1);

namespace App\Events\Loan;

use App\Entities\CustomerEntity;
use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;
use App\Entities\PaymentOrder;
use Symfony\Contracts\EventDispatcher\Event;

class LoanPaidEvent extends Event
{

    public const NAME = 'loan.paid';

    public function __construct(protected PaymentEntity   $payment,
                                protected LoanEntity      $loan,
                                protected ?CustomerEntity $customer,
                                protected ?PaymentOrder   $refund,
    )
    {
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
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