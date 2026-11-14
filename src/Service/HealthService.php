<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Server health: shows over which feeds at least one cycle of updates was consumed.
 */
final class HealthService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function health(): HealthResponse
    {
        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v1/health'),
            HealthResponse::class
        );
    }
}
