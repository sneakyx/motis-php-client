<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use DateTimeImmutable;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Plan200Response;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Routing: computes optimal connections from one place to another.
 *
 * @see https://github.com/motis-project/motis/blob/master/openapi.yaml GET /api/v6/plan
 */
final class PlanService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @param string                 $fromPlace       "latitude,longitude[,level]" or a stop ID
     * @param string                 $toPlace         "latitude,longitude[,level]" or a stop ID
     * @param DateTimeImmutable|null $time            departure time (arrival time when $arriveBy is true); server default: now
     * @param bool                   $arriveBy        search backwards from $time instead of forwards
     * @param int|null               $maxTransfers    maximum number of allowed transfers
     * @param int|null               $maxTravelTime   maximum travel time in minutes
     * @param int|null               $minTransferTime minimum transfer time for each transfer in minutes
     * @param list<string>           $via             up to two via stop IDs
     * @param list<string>           $transitModes    allowed transit modes, e.g. ["TRANSIT"] or ["SUBWAY", "TRAM"]
     * @param array<string, mixed>   $extraQuery      any additional plan endpoint query parameters
     */
    public function plan(
        string $fromPlace,
        string $toPlace,
        ?DateTimeImmutable $time = null,
        bool $arriveBy = false,
        ?int $maxTransfers = null,
        ?int $maxTravelTime = null,
        ?int $minTransferTime = null,
        array $via = [],
        array $transitModes = [],
        array $extraQuery = [],
    ): Plan200Response {
        $query = \array_merge($extraQuery, [
            'fromPlace' => $fromPlace,
            'toPlace' => $toPlace,
            'time' => $time,
            'arriveBy' => $arriveBy,
            'maxTransfers' => $maxTransfers,
            'maxTravelTime' => $maxTravelTime,
            'minTransferTime' => $minTransferTime,
            'via' => $via,
            'transitModes' => $transitModes,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v6/plan', $query),
            Plan200Response::class
        );
    }
}
