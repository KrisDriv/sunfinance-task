<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Enums\PaymentOrderStatus;
use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;
use App\Entities\PaymentOrder;
use App\Events\Refund\RefundQueuedEvent;
use App\Exceptions\Refund\InvalidRefundTargetException;
use App\Tables\CustomerTable;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RefundService
{

    public function __construct(private readonly CustomerTable       $customerTable,
                                private readonly LoggerInterface     $logger,
                                private readonly PaymentOrderService $paymentOrderService,
                                private readonly EventDispatcher     $eventDispatcher)
    {
    }

    /**
     * @throws InvalidRefundTargetException
     */
    public function issueRefundOnOverpaidLoan(PaymentEntity $paymentEntity, ?LoanEntity $loanEntity, float $refundAmount): ?PaymentOrder
    {
        $customer = $this->customerTable->findById($loanEntity->customer_id);

        if ($customer === null) {


            throw new InvalidRefundTargetException($paymentEntity,
                "Unable to find customer by id '$loanEntity->customer_id' for a refund"
            );
        }

        $paymentOrder = new PaymentOrder(
            $refundAmount,
            $customer->id,
            PaymentOrderStatus::PENDING
        );

        if ($this->paymentOrderService->queue($paymentOrder)) {
            $this->logger->info('Refund payment order queued', [
                'paymentOrder' => $paymentOrder
            ]);

            $this->eventDispatcher->dispatch(new RefundQueuedEvent($paymentOrder), RefundQueuedEvent::NAME);

            return $paymentOrder;
        }

        return null;
    }

}