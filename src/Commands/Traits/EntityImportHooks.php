<?php

namespace App\Commands\Traits;

use Composite\Entity\AbstractEntity;
use Symfony\Component\Console\Output\OutputInterface;

trait EntityImportHooks
{

    /**
     * Before entity is saved
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    protected function preSave(AbstractEntity $entity, OutputInterface $output): ?int
    {
        return null;
    }

    /**
     * Before row is hydrated. Note: Passed array has translated keys
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    protected function preHydrate(array &$row, OutputInterface $output): ?int
    {
        return null;
    }

    /**
     * When entity already exists in database.
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    protected function onDuplicate(AbstractEntity $entity, OutputInterface $output): ?int
    {
        return null;
    }

}