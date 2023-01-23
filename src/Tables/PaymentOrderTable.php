<?php

declare(strict_types=1);

namespace App\Tables;

use App\Entities\PaymentOrder;
use Composite\DB\AbstractTable;
use Composite\DB\TableConfig;

class PaymentOrderTable extends AbstractTable
{
	protected function getConfig(): TableConfig
	{
		return TableConfig::fromEntitySchema(PaymentOrder::schema());
	}


	public function findByPk(int $id): ?PaymentOrder
	{
		return $this->createEntity($this->findByPkInternal($id));
	}


	/**
	 * @return PaymentOrder[]
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
