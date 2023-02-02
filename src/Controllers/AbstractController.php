<?php
declare(strict_types=1);

namespace App\Controllers;

use JsonSerializable;
use Symfony\Component\HttpFoundation\Response;

class AbstractController
{

    public function response(mixed $content, ?int $statusCode = Response::HTTP_OK, ?string $message = null): Response
    {
        $response = new Response();

        $content = match (true) {
            is_array($content) => json_encode($content, JSON_PRETTY_PRINT),
            is_object($content) =>
            $content instanceof JsonSerializable
                ? $content->jsonSerialize()
                : json_encode($content, JSON_PRETTY_PRINT),
            default => $content
        };

        return $response->setContent($content)->setStatusCode($statusCode, $message);
    }

}