<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\StopInfo200Response;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Timetable: information about a single stop.
 */
final class StopService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @param bool                 $center     return the stop area centers instead of the stop positions
     * @param float|null           $radius     search radius in meters
     * @param bool|null            $exactRadius return the exact search radius that was used
     * @param list<string>         $language   language tags (e.g. ["de"])
     * @param array<string, mixed> $extraQuery any additional stop endpoint query parameters
     */
    public function stop(
        string $stopId,
        bool $center = false,
        ?float $radius = null,
        ?bool $exactRadius = null,
        array $language = [],
        array $extraQuery = [],
    ): StopInfo200Response {
        $query = \array_merge($extraQuery, [
            'stopId' => $stopId,
            'center' => $center,
            'radius' => $radius,
            'exactRadius' => $exactRadius,
            'language' => $language,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v6/stop', $query),
            StopInfo200Response::class
        );
    }
}
