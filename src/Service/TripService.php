<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Timetable: retrieves a single trip as itinerary.
 */
final class TripService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @param bool                 $withScheduledSkippedStops include intermediate stops that are not boardable/alightable by schedule
     * @param bool|null            $detailedLegs             return legGeometry for transit legs (server default: true)
     * @param bool|null            $joinInterlinedLegs        merge stay-seated transfers into one leg (server default: true)
     * @param array<string, mixed> $extraQuery                any additional trip endpoint query parameters
     */
    public function trip(
        string $tripId,
        bool $withScheduledSkippedStops = false,
        ?bool $detailedLegs = null,
        ?bool $joinInterlinedLegs = null,
        array $extraQuery = [],
    ): Itinerary {
        $query = \array_merge($extraQuery, [
            'tripId' => $tripId,
            'withScheduledSkippedStops' => $withScheduledSkippedStops,
            'detailedLegs' => $detailedLegs,
            'joinInterlinedLegs' => $joinInterlinedLegs,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v6/trip', $query),
            Itinerary::class
        );
    }
}
