<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Exception;

/**
 * Thrown when the MOTIS server answers with an HTTP error status code (>= 400).
 */
final class MotisApiException extends MotisException
{
    public function __construct(
        private readonly string $motisErrorMessage,
        private readonly int $statusCode,
        private readonly string $responseBody,
    ) {
        parent::__construct(\sprintf(
            'MOTIS API request failed with HTTP status %d: %s',
            $statusCode,
            $motisErrorMessage !== '' ? $motisErrorMessage : \trim($responseBody)
        ));
    }

    /**
     * Error message from the MOTIS server (content of the "error" property of
     * the JSON error payload). Empty if the response contained no such property.
     */
    public function getMotisErrorMessage(): string
    {
        return $this->motisErrorMessage;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Raw (unparsed) HTTP response body.
     */
    public function getResponseBody(): string
    {
        return $this->responseBody;
    }
}
