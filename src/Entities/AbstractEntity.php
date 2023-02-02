<?php

namespace App\Entities;

class AbstractEntity extends \Composite\Entity\AbstractEntity
{

    public function isSaved(): bool
    {
        return !$this->isNew();
    }

}