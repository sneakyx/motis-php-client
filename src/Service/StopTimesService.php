<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use DateTimeImmutable;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Stoptimes200Response;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Timetable: departure and arrival boards for a stop.
 */
final class StopTimesService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * @param DateTimeImmutable|null $time        departure time (or arrival time when $arriveBy); server default: now
     * @param bool                   $arriveBy    return arrivals instead of departures
     * @param bool                   $both        return both, departures and arrivals
     * @param int|null               $n           number of results (server default: 10)
     * @param string|null            $pageCursor  pagination cursor from a previous response
     * @param array<string, mixed>   $extraQuery  any additional stoptimes endpoint query parameters
     */
    public function stoptimes(
        string $stopId,
        ?DateTimeImmutable $time = null,
        bool $arriveBy = false,
        bool $both = false,
        ?int $n = null,
        ?string $pageCursor = null,
        array $extraQuery = [],
    ): Stoptimes200Response {
        $query = \array_merge($extraQuery, [
            'stopId' => $stopId,
            'time' => $time,
            'arriveBy' => $arriveBy,
            'both' => $both,
            'n' => $n,
            'pageCursor' => $pageCursor,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v6/stoptimes', $query),
            Stoptimes200Response::class
        );
    }
}
