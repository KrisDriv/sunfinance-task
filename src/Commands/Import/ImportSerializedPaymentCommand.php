<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Entities\PaymentEntity;
use App\Tables\PaymentTable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'import:payments',
    description: 'Imports payments from a file',
    hidden: false
)]
class ImportSerializedPaymentCommand extends ImportSerializedEntityCommand
{

    public const ENTITY_CLASS = PaymentEntity::class;
    public const ENTITY_TABLE = PaymentTable::class;

}