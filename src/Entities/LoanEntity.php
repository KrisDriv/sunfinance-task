<?php declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;
use Composite\DB\Attributes\{Table, PrimaryKey, Column};
use Composite\Entity\AbstractEntity;
use App\Entities\Enums\LoanState;
use Composite\DB\Traits;

#[Table(connection: 'mysql', name: 'loans')]
class LoanEntity extends AbstractEntity
{
    use Traits\SoftDelete;

    public function __construct(
        #[PrimaryKey]
        #[Column(size: 36)]
        public readonly string            $id,
        #[Column(size: 36)]
        public string                     $customer_id,
        public string                     $reference,
        public LoanState                  $state,
        #[Column(precision: 13, scale: 2)]
        public float                      $amount_issued,
        #[Column(precision: 13, scale: 2)]
        public float                      $amount_to_pay,
        public DateTimeImmutable          $updated_at = new DateTimeImmutable(),
        public readonly DateTimeImmutable $created_at = new DateTimeImmutable(),
    )
    {
    }
}
