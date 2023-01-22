<?php

namespace App\Tables;

use Composite\Entity\AbstractEntity;

abstract class AbstractTable extends \Composite\DB\AbstractTable
{

    public function exists(string|int|array|AbstractEntity $data): bool
    {
        return $this->findByPkInternal($data) !== null;
    }

}