<?php declare(strict_types=1);

namespace App\Entities;

use DateTimeImmutable;
use Composite\DB\Attributes\{Table, PrimaryKey, Column, Index};
use Composite\Entity\AbstractEntity;
use App\Entities\Enums\PaymentStatus;
use Composite\DB\Traits;

#[Table(connection: 'mysql', name: 'payments')]
#[Index(columns: ['payment_reference'], isUnique: true, name: 'payment_reference')]
class PaymentEntity extends AbstractEntity
{
    use Traits\SoftDelete;

    #[PrimaryKey(autoIncrement: true)]
    public readonly int $id;

    public function __construct(
        public string $payer_name,
        public string $payer_surname,
        public DateTimeImmutable $payment_date,
        #[Column(precision: 13, scale: 2)]
        public float $amount,
        #[Column(size: 11)]
        public string $national_security_number,
        public string $description,
        #[Column(size: 16)]
        public string $payment_reference,
        public ?PaymentStatus $status = null,
        public readonly DateTimeImmutable $created_at = new DateTimeImmutable(),
        public DateTimeImmutable $updated_at = new DateTimeImmutable(),
    ) {}
}
