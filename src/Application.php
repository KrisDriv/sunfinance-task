<?php
declare(strict_types=1);

namespace KrisDriv\TaskSunfinance;

use Composite\DB\ConnectionManager;

class Application
{

    public function __construct(private ConnectionManager $connectionManager)
    {

    }

}