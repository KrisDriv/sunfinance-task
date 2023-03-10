<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Entities\PaymentEntity;
use App\Exceptions\Import\ImportException;
use App\Exceptions\Payment\InvalidPaymentTargetException;
use App\Exceptions\Refund\InvalidRefundTargetException;
use App\Services\PaymentProcessorService;
use App\Tables\PaymentTable;
use Composite\Entity\AbstractEntity;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'import:payments',
    description: 'Imports payments from a file',
    hidden: false
)]
class ImportSerializedPaymentCommand extends ImportSerializedEntityCommand
{

    public const ENTITY_CLASS = PaymentEntity::class;
    public const ENTITY_TABLE = PaymentTable::class;

    private PaymentProcessorService $paymentProcessor;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->paymentProcessor = $this->application->getContainer()->get(PaymentProcessorService::class);
    }

    /**
     * @throws ImportException
     */
    public function onDuplicate(AbstractEntity $entity, OutputInterface $output): ?int
    {
        throw new ImportException($entity, 'Duplicate payment', code: 1);
    }

    /**
     * @throws Exception
     */
    public function preHydrate(array &$row, OutputInterface $output): ?int
    {
        if (strtotime($row['payment_date']) === false) {
            throw new ImportException($row, 'Invalid date: ' . $row['payment_date'], code: 3);
        }

        if (((int)$row['amount']) < 0) {
            throw new ImportException($row, 'Negative amount: ' . $row['amount'], code: 2);
        }

        return null;
    }

    /**
     * @throws InvalidPaymentTargetException
     * @throws InvalidRefundTargetException
     */
    public function preSave(PaymentEntity|AbstractEntity $entity, OutputInterface $output): ?int
    {
        $this->paymentProcessor->processNewLoanPayment($entity);

        return null;
    }

}