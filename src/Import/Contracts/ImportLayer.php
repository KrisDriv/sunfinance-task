<?php
declare(strict_types=1);

namespace App\Import\Contracts;

use App\Entities\AbstractEntity;
use App\Tables\AbstractTable;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Nudges between hydration and saving steps of import operation.
 */
abstract class ImportLayer
{

    /**
     * Before entity is saved
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    public function preSave(AbstractEntity $entity, AbstractTable $table): ?int
    {
        return null;
    }

    /**
     * Before row is hydrated. Note: Passed array has translated keys. At this step transform & validation
     * will be performed
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    public function preHydrate(array &$row, AbstractTable $table): ?int
    {
        $original = $row;
        if ($this instanceof TransformsFields) {
            foreach ($row as $field => &$data) {
                $data = $this->transformField($field, $data, $original, $table);
            }
        }

        // TODO: Collect errors
        if ($this instanceof ValidatesFields) {
            foreach ($row as $field => $data) {
                $this->validateField($field, $data, $original, $table);
            }
        }


        return null;
    }

    /**
     * When entity already exists in database.
     *
     * To exit program prematurely return exit code
     *
     * @return null|int exit code
     */
    public function onDuplicate(AbstractEntity $entity, AbstractTable $table): ?int
    {
        return null;
    }


}