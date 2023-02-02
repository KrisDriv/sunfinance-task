<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Entities\CustomerEntity;
use App\Tables\CustomerTable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'import:customers',
    description: 'Imports customers from a file',
    hidden: false
)]
class ImportSerializedCustomerCommand extends ImportSerializedEntityCommand
{

    public const ENTITY_CLASS = CustomerEntity::class;
    public const ENTITY_TABLE = CustomerTable::class;

}