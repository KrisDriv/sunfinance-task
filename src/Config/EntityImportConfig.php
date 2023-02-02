<?php

namespace App\Config;

use App\Entities\CustomerEntity;
use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;

class EntityImportConfig
{

    public const TRANSLATIONS = [
        PaymentEntity::class => [],

        LoanEntity::class => [],

        CustomerEntity::class => [
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'ssn' => 'social_security_number',
        ],
    ];

    public function getKeyTranslationArrayFor(string $entityClass): array
    {
        return static::TRANSLATIONS[$entityClass] ?? [];
    }

}