<?php
declare(strict_types=1);

namespace App\Commands;

use App\Commands\Bases\ImportSerializedEntityCommand;
use App\Entities\CustomerEntity;
use App\Tables\CustomerTable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'import:customers',
    description: 'Imports customers from JSON file',
    hidden: false
)]
class ImportCustomerEntityCommand extends ImportSerializedEntityCommand
{

    public const KEY_TRANSLATIONS = [
        'firstname' => 'first_name',
        'lastname' => 'last_name',
        'ssn' => 'social_security_number',
    ];

    public const ENTITY_CLASS = CustomerEntity::class;
    public const ENTITY_TABLE = CustomerTable::class;

}