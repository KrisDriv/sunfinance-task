<?php

declare(strict_types=1);

namespace App\Tables;

use App\Entities\PaymentEntity;
use Composite\DB\TableConfig;
use Composite\Entity\Exceptions\EntityException;

class PaymentTable extends AbstractTable
{
    /**
     * @throws EntityException
     */
    protected function getConfig(): TableConfig
    {
        return TableConfig::fromEntitySchema(PaymentEntity::schema());
    }

    public function findByPk(int $id): ?PaymentEntity
    {
        return $this->createEntity($this->findByPkInternal($id));
    }

    /**
     * @return PaymentEntity[]
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
