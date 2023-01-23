<?php
declare(strict_types=1);

namespace App\Services;

use App\Entities\Enums\PaymentOrderStatus;
use App\Entities\PaymentOrder;
use App\Tables\PaymentOrderTable;
use Psr\Log\LoggerInterface;
use Throwable;

class PaymentOrderService
{

    public function __construct(private readonly PaymentOrderTable $paymentOrderTable,
                                private readonly LoggerInterface   $logger)
    {
    }

    public function queue(PaymentOrder $paymentOrder): bool
    {
        if (!$paymentOrder->isNew() && $paymentOrder->status !== PaymentOrderStatus::PENDING) {
            $this->logger->notice("Re-queued payment order with status {$paymentOrder->status->name}", [
                'paymentOrder' => $paymentOrder
            ]);
        }

        $paymentOrder->status = PaymentOrderStatus::PENDING;

        try {
            $this->paymentOrderTable->save($paymentOrder);
        } catch (Throwable $e) {
            $this->logger->error('Unable to queue payment order', ['exception' => $e, 'paymentOrder' => $paymentOrder]);

            return false;
        }

        return true;
    }

}