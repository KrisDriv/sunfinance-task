<?php declare(strict_types=1);

namespace App\Entities;

use Composite\DB\Attributes\{Column, PrimaryKey, Table};
use Composite\DB\Traits;
use DateTimeImmutable;

#[Table(connection: 'mysql', name: 'customers')]
class CustomerEntity extends AbstractEntity
{
    use Traits\SoftDelete;

    public function __construct(
        #[PrimaryKey]
        public string                     $id,
        #[Column(size: 36)]
        public string                     $first_name,
        public string                     $last_name,
        #[Column(size: 16)]
        public string                     $social_security_number,
        #[Column(size: 319)]
        public ?string                    $email,
        #[Column(size: 20)]
        public ?string                    $phone,
        public readonly DateTimeImmutable $created_at = new DateTimeImmutable(),
        public DateTimeImmutable          $updated_at = new DateTimeImmutable(),
    )
    {
    }
}
