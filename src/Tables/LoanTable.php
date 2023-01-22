<?php

declare(strict_types=1);

namespace App\Tables;

use App\Entities\LoanEntity;
use Composite\DB\TableConfig;
use Composite\Entity\Exceptions\EntityException;

class LoanTable extends AbstractTable
{
    /**
     * @throws EntityException
     */
    protected function getConfig(): TableConfig
    {
        return TableConfig::fromEntitySchema(LoanEntity::schema());
    }

    public function findByPk(string $id): ?LoanEntity
    {
        return $this->createEntity($this->findByPkInternal($id));
    }

    /**
     * @return LoanEntity[]
     */
    public function findAll(): array
    {
        return $this->createEntities($this->findAllInternal());
    }

    public function countAll(): int
    {
        return $this->countAllInternal();
    }
}
