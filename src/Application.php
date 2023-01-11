<?php
declare(strict_types=1);

namespace App;

use Composite\DB\ConnectionManager;

class Application
{

    public function __construct(private ConnectionManager $connectionManager)
    {

    }

}