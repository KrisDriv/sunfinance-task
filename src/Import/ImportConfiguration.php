<?php
declare(strict_types=1);

namespace App\Import;

use App\Import\Contracts\ImportLayer;
use App\Tables\AbstractTable;

class ImportConfiguration
{

    /**
     * @param array $rows
     * @param string $entityClass
     * @param AbstractTable $table
     * @param ImportLayer[] $layers
     */
    public function __construct(public readonly array         $rows,
                                public readonly string        $entityClass,
                                public readonly AbstractTable $table,
                                public readonly array         $layers,
                                public readonly ?array        $translationKeys = null)
    {
    }

}