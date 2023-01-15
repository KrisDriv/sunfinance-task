<?php
declare(strict_types=1);

/** @var Application $app */
$app = require_once 'bootstrap.php';

use App\Application;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

$config = new PhpFile('config/migrations.php');

$conn = $app->getConnection();

return DependencyFactory::fromConnection($config, new ExistingConnection($conn));