<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Import\ImportException;
use App\Exceptions\Import\ImportTransformException;

class PaymentImportService
{

    /**
     * @throws ImportException
     */
    public function validate(array $row): void
    {
        if (strtotime($row['payment_date']) === false) {
            throw new ImportException($row, 'Invalid date: ' . $row['payment_date'], code: 3);
        }

        if (((int)$row['amount']) < 0) {
            throw new ImportException($row, 'Negative amount: ' . $row['amount'], code: 2);
        }
    }

    /**
     * @throws ImportTransformException
     */
    public function transform(&$data): void
    {
        $reference = $this->readPaymentReference($data['payment_reference']);
        $reference ??= $this->readPaymentReference($data['description']);

        if (!$reference) {
            throw new ImportTransformException(
                $data,
                "Unable to read payment reference (payment_reference: "
                . ($data['payment_reference'] ?? 'undefined')
                . ")"
            );
        }

        $data['payment_reference'] = $reference;
    }

    private function readPaymentReference(string $string): ?string
    {
        preg_match('/LV\d{1,8}/', $string, $matches);

        return $matches[0] ?? null;
    }

}