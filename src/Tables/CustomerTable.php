<?php

declare(strict_types=1);

namespace App\Tables;

use App\Entities\CustomerEntity;
use Composite\DB\AbstractTable;
use Composite\DB\TableConfig;

class CustomerTable extends AbstractTable
{
	protected function getConfig(): TableConfig
	{
		return TableConfig::fromEntitySchema(CustomerEntity::schema());
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
