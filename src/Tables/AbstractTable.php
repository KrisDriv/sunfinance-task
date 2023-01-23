<?php
declare(strict_types=1);

namespace App\Tables;

use Composite\Entity\AbstractEntity;

abstract class AbstractTable extends \Composite\DB\AbstractTable
{

    /**
     * Returns true if any unique column matches
     *
     * @param string|int|array|AbstractEntity $data
     * @return bool
     */
    public function exists(string|int|array|AbstractEntity $data): bool
    {
        $where = $this->getPkCondition($data);

        return $this->findOneInternal(array_filter($where)) !== null;
    }

}