<?php

namespace App\Services;

use InvalidArgumentException;
use JsonException;

class JsonFileReaderService
{

    /**
     * Reads and decodes JSON file
     *
     * @return array associated
     * @throws JsonException|InvalidArgumentException
     */
    public function readFile(string $relativeFilePath): array
    {
        $absoluteFilePath = base_path($relativeFilePath);

        if (!file_exists($absoluteFilePath)) {
            throw new InvalidArgumentException("File '$absoluteFilePath' does not exist");
        }

        return json_decode(
            file_get_contents($absoluteFilePath),
            true,
            flags: JSON_THROW_ON_ERROR
        );
    }

}