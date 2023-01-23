<?php
declare(strict_types=1);

/** @var Application $app */
if (!isset($app)) {
    $app = require_once 'bootstrap/app.php';
}

use App\Application;
use Composite\DoctrineMigrations\SchemaProviderBridge;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Provider\SchemaProvider;
use Illuminate\Support\Facades\DB;

$config = new PhpFile('config/migrations.php');

$connection = $app->getConnection();

$dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection($connection));

$dependencyFactory->setDefinition(SchemaProvider::class, static fn() => new SchemaProviderBridge(
    entityDirs: [
        __DIR__ . '/src',
    ],
    connectionName: 'mysql',
    connection: $connection,
));

return $dependencyFactory;