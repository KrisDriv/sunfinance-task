<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Enums\LoanState;
use App\Entities\Enums\PaymentStatus;
use App\Entities\PaymentEntity;
use App\Events\FailedPaymentEvent;
use App\Events\LoanPaidEvent;
use App\Events\PaymentReceivedEvent;
use App\Exceptions\Payment\InvalidPaymentTargetException;
use App\Exceptions\Refund\InvalidRefundTargetException;
use App\Tables\LoanTable;
use App\Tables\PaymentTable;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

class PaymentProcessorService
{

    public function __construct(
        private readonly LoanTable       $loanTable,
        private readonly PaymentTable    $paymentTable,
        private readonly RefundService   $refundService,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcher $eventDispatcher
    )
    {
    }

    /**
     * @throws InvalidPaymentTargetException
     * @throws InvalidRefundTargetException
     */
    public function processNewLoanPayment(PaymentEntity $paymentEntity): ?PaymentEntity
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
                $paymentOrder = $this->refundService->issueRefundOnOverpaidLoan($paymentEntity, $loanEntity, (float)($amountAfterPayment * -1));
                $paymentEntity->status = PaymentStatus::PARTIALLY_ASSIGNED;

                if ($paymentOrder === null) {
                    $this->logger->error('Loan payment processing failed. Incoming amount exceeded expected amount, but no refund was queued.', [
                        'paymentEntity' => $paymentEntity,
                        'loanEntity' => $loanEntity
                    ]);

                    $this->eventDispatcher->dispatch(new FailedPaymentEvent($paymentEntity), FailedPaymentEvent::NAME);

                    return null;
                }
            }
        }

        $loanEntity->amount_to_pay = max(0, $amountAfterPayment);

        try {
            $this->loanTable->save($loanEntity);
            $this->paymentTable->save($paymentEntity);

            $this->logger->info("Loan payment processed", [
                'loanEntity' => $loanEntity,
                'paymentEntity' => $paymentEntity
            ]);

            if ($loanEntity->state === LoanState::PAID) {
                $this->eventDispatcher->dispatch(
                    new LoanPaidEvent($paymentEntity, $loanEntity, $paymentOrder ?? null),
                    LoanPaidEvent::NAME
                );
            }

            $this->eventDispatcher->dispatch(new PaymentReceivedEvent($paymentEntity), PaymentReceivedEvent::NAME);

            return $paymentEntity;
        } catch (Throwable $e) {
            $this->logger->error('Loan payment processing failed. Exception occurred while writing to database', [
                'loanEntity' => $loanEntity,
                'paymentEntity' => $paymentEntity,
                'exception' => $e
            ]);

            $this->eventDispatcher->dispatch(new FailedPaymentEvent($paymentEntity));
        }

        return null;
    }

}