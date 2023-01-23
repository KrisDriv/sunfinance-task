<?php

namespace App\Listeners\Concrete;

class Listener
{

    protected array $handlers = [];

    public function getHandlers(): array
    {
        return $this->handlers;
    }

}