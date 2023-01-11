<?php
declare(strict_types=1);

return [
    'sqlite' => [
        'driver' => 'pdo_sqlite',
        'path' => database_path('database.sqlite'),
    ],
    'mysql' => [
        'driver' => 'pdo_mysql',
        'dbname' => env('MYSQL_DATABASE_NAME'),
        'user' => env('MYSQL_USER'),
        'password' => env('MYSQL_PASSWORD'),
        'host' => env('MYSQL_HOST'),
    ],
    'postgres' => [
        'driver' => 'pdo_pgsql',
        'dbname' => env('POSTGRES_DATABASE_NAME'),
        'user' => env('POSTGRES_USER'),
        'password' => env('POSTGRES_PASSWORD'),
        'host' => env('POSTGRES_HOST'),
    ],
];