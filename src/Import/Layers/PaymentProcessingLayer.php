<?php

namespace App\Import\Layers;

use App\Entities\PaymentEntity;
use App\Exceptions\Import\ImportException;
use App\Exceptions\Payment\InvalidPaymentTargetException;
use App\Exceptions\Refund\InvalidRefundTargetException;
use App\Import\Contracts\ImportLayer;
use App\Services\PaymentProcessorService;
use App\Tables\AbstractTable;
use Composite\Entity\AbstractEntity;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentProcessingLayer extends ImportLayer
{

    public function __construct(private readonly PaymentProcessorService $paymentProcessor)
    {
    }

    /**
     * @throws ImportException
     */
    public function onDuplicate(AbstractEntity $entity, AbstractTable $table): ?int
    {
        throw new ImportException($entity, 'Duplicate payment', code: 1);
    }

    /**
     * @throws InvalidPaymentTargetException
     * @throws InvalidRefundTargetException
     */
    public function preSave(PaymentEntity|AbstractEntity $entity, AbstractTable $table): ?int
    {
        $this->paymentProcessor->processNewLoanPayment($entity);

        return null;
    }

}