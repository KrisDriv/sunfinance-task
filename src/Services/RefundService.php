<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;
use App\Exceptions\Refund\InvalidRefundTargetException;
use App\Tables\CustomerTable;

class RefundService
{

    public function __construct(private readonly CustomerTable $customerTable)
    {
    }

    /**
     * @throws InvalidRefundTargetException
     */
    public function issueRefundOnOverpaidLoan(PaymentEntity $paymentEntity, ?LoanEntity $loanEntity, float $refundAmount): void
    {
        $customer = $this->customerTable->findById($loanEntity->customer_id);

        if ($customer === null) {
            throw new InvalidRefundTargetException($paymentEntity, 'Unable to find customer for a refund');
        }

        dd($customer);

        // MAKE A PaymentOrder
    }

}