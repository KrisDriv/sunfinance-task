<?php declare(strict_types=1);

namespace App\Entities;

use Composite\DB\Attributes\{Table, Column};
use Composite\Entity\AbstractEntity;
use Composite\DB\Traits;

#[Table(connection: 'mysql', name: 'customers')]
class Customer extends AbstractEntity
{
    use Traits\SoftDelete;

    public function __construct(
        #[Column(size: 36)]
        public string $id,
        public string $first_name,
        public string $last_name,
        #[Column(size: 16)]
        public string $social_security_number,
        #[Column(size: 319)]
        public string $email,
        public readonly \DateTimeImmutable $created_at = new \DateTimeImmutable(),
        public \DateTimeImmutable $updated_at = new \DateTimeImmutable(),
    ) {}
}
