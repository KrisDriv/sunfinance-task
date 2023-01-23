<?php
declare(strict_types=1);

namespace App\Commands\Import;

use App\Entities\LoanEntity;
use App\Services\EntityHydrateService;
use App\Tables\LoanTable;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'import:loans',
    description: 'Imports loans from a file',
    hidden: false
)]
class ImportSerializedLoanCommand extends ImportSerializedEntityCommand
{

    /**
     * This is empty as most keys align with those found in database table. Few that do not
     * are converted to snake case automagically
     *
     * @see EntityHydrateService::translateKeys
     */
    public const KEY_TRANSLATIONS = [];

    public const ENTITY_CLASS = LoanEntity::class;
    public const ENTITY_TABLE = LoanTable::class;

}