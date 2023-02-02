<?php declare(strict_types=1);

namespace App\Entities;

use Composite\DB\Attributes\{Table, PrimaryKey, Column};
use App\Entities\Enums\PaymentOrderStatus;
use Composite\DB\Traits;

#[Table(connection: 'mysql', name: 'payment_orders')]
class PaymentOrder extends AbstractEntity
{
    use Traits\SoftDelete;

    #[PrimaryKey(autoIncrement: true)]
    public readonly int $id;

    public function __construct(
        #[Column(precision: 13, scale: 2)]
        public float                       $amount,
        #[Column(size: 36)]
        public string                      $customer_id,
        public PaymentOrderStatus          $status,
        public readonly \DateTimeImmutable $created_at = new \DateTimeImmutable(),
        public \DateTimeImmutable          $updated_at = new \DateTimeImmutable(),
    )
    {
    }
}
