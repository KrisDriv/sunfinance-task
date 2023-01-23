<?php

declare(strict_types=1);

namespace App\Tables;

use App\Entities\CustomerEntity;
use Composite\DB\TableConfig;
use Composite\Entity\Exceptions\EntityException;

class CustomerTable extends AbstractTable
{

    /**
     * @throws EntityException
     */
    protected function getConfig(): TableConfig
    {
        return TableConfig::fromEntitySchema(CustomerEntity::schema());
    }

    public function findById(string $customerId): ?CustomerEntity
    {
        return $this->createEntity($this->findOneInternal(['id' => $customerId]));
    }

    public function findByPk(): ?CustomerEntity
    {
        return $this->createEntity($this->findOneInternal([]));
    }

    /**
     * @return CustomerEntity[]
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
