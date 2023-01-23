<?php

namespace App\Services;

use InvalidArgumentException;
use ParseCsv\Csv;

class CsvFileReaderService
{

    /**
     * Reads and decodes JSON file
     *
     * @return array associated
     * @throws InvalidArgumentException
     */
    public function readFile(string $relativeFilePath): array
    {
        $absoluteFilePath = base_path($relativeFilePath);

        $csv = new Csv();
        $csv->auto($absoluteFilePath);

        return $csv->data;
    }

}