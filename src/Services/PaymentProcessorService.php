<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Enums\LoanState;
use App\Entities\Enums\PaymentStatus;
use App\Entities\PaymentEntity;
use App\Exceptions\Payment\InvalidPaymentTargetException;
use App\Tables\LoanTable;
use App\Tables\PaymentTable;
use Throwable;

class PaymentProcessorService
{

    public function __construct(
        private readonly LoanTable     $loanTable,
        private readonly PaymentTable  $paymentTable,
        private readonly RefundService $refundService,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function processNewPayment(PaymentEntity $paymentEntity): void
    {
        $loanEntity = $this->loanTable->findByReference(strtoupper($paymentEntity->description));

        if (!$loanEntity) {
            throw new InvalidPaymentTargetException($paymentEntity, 'Could not find a target loan');
        }

        $amountAfterPayment = $loanEntity->amount_to_pay - $paymentEntity->amount;

        $paymentEntity->status = PaymentStatus::ASSIGNED;

        if ($amountAfterPayment <= 0) {
            $loanEntity->state = LoanState::PAID;

            if ($amountAfterPayment < 0) {
                $this->refundService->issueRefundOnOverpaidLoan($paymentEntity, $loanEntity, (float)($amountAfterPayment * -1));
                $paymentEntity->status = PaymentStatus::PARTIALLY_ASSIGNED;
            }
        }

        $loanEntity->amount_to_pay = max(0, $amountAfterPayment);

        $this->loanTable->save($loanEntity);
        $this->paymentTable->save($paymentEntity);
    }

}