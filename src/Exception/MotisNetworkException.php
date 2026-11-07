<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;

/**
 * Thrown when the HTTP transport fails before a response was received,
 * e.g. DNS errors, connection refused, timeouts.
 */
final class MotisNetworkException extends MotisException
{
    public function __construct(string $requestTarget, ClientExceptionInterface $previous)
    {
        parent::__construct(
            \sprintf(
                'MOTIS request to "%s" failed on transport level: %s',
                $requestTarget,
                $previous->getMessage()
            ),
            0,
            $previous
        );
    }

    public function getTransportException(): ClientExceptionInterface
    {
        \assert($this->getPrevious() instanceof ClientExceptionInterface);

        return $this->getPrevious();
    }
}
