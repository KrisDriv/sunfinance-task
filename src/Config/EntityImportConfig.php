<?php
declare(strict_types=1);

namespace App\Config;

use App\Application;
use App\Entities\CustomerEntity;
use App\Entities\LoanEntity;
use App\Entities\PaymentEntity;
use App\Import\Contracts\ImportLayer;
use App\Import\Layers\PaymentProcessingLayer;
use App\Import\Layers\PaymentTransformationLayer;
use App\Import\Layers\PaymentValidationLayer;
use DI\DependencyException;
use DI\NotFoundException;

class EntityImportConfig
{

    public function __construct(private readonly Application $application)
    {
    }

    public const TRANSLATIONS = [
        PaymentEntity::class => [
            'firstname' => 'payer_name',
            'lastname' => 'payer_surname'
        ],

        LoanEntity::class => [],

        CustomerEntity::class => [
            'firstname' => 'first_name',
            'lastname' => 'last_name',
            'ssn' => 'social_security_number',
        ],
    ];

    public const LAYERS = [
        PaymentEntity::class => [
            PaymentTransformationLayer::class,
            PaymentValidationLayer::class,
            PaymentProcessingLayer::class
        ]
    ];

    public function getKeyTranslationArrayFor(string $entityClass): array
    {
        return static::TRANSLATIONS[$entityClass] ?? [];
    }

    /**
     * @param string $entityClass
     *
     * @return ImportLayer[]
     */
    public function getLayersFor(string $entityClass): array
    {
        try {
            return array_map(
                fn(string $layerClass) => $this->application->getContainer()->make($layerClass),
                static::LAYERS[$entityClass] ?? []
            );
        } catch (DependencyException|NotFoundException $e) {
            $this->application->getLogger()->critical(
                sprintf(
                    "Unable to resolve import layers for entity class '%s'. %s",
                    $entityClass, $e->getMessage()
                ),
                [
                    'exception' => $e,
                    'entityClass' => $entityClass
                ]
            );
        }

        return [];
    }

}