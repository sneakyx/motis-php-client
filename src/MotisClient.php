<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient;

use DateTimeImmutable;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\Model\Plan200Response;
use Sneakyx\MotisPhpClient\Model\StopInfo200Response;
use Sneakyx\MotisPhpClient\Model\Stoptimes200Response;
use Sneakyx\MotisPhpClient\Service\GeocodeService;
use Sneakyx\MotisPhpClient\Service\HealthService;
use Sneakyx\MotisPhpClient\Service\PlanService;
use Sneakyx\MotisPhpClient\Service\StopService;
use Sneakyx\MotisPhpClient\Service\StopTimesService;
use Sneakyx\MotisPhpClient\Service\TripService;

/**
 * Facade for the MOTIS API.
 *
 * Works with any PSR-18 HTTP client; defaults to Guzzle. Point it at any MOTIS
 * server, e.g. the community-run Transitous instance (https://api.transitous.org)
 * or a self-hosted MOTIS instance.
 *
 * Example:
 *
 *     $client = new MotisClient('https://api.transitous.org');
 *     $response = $client->plan('50.1109,8.6821', 'Frankfurt (Main) Hauptwache');
 *     foreach ($response->getItineraries() as $itinerary) {
 *         // ...
 *     }
 */
final class MotisClient
{
    public const DEFAULT_BASE_URL = 'https://api.transitous.org';

    private readonly PlanService $planService;
    private readonly GeocodeService $geocodeService;
    private readonly TripService $tripService;
    private readonly StopTimesService $stopTimesService;
    private readonly StopService $stopService;
    private readonly HealthService $healthService;

    public function __construct(
        string $baseUrl = self::DEFAULT_BASE_URL,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
    ) {
        $apiClient = new ApiClient(
            $httpClient ?? new GuzzleClient(),
            $requestFactory ?? new HttpFactory(),
            $baseUrl
        );

        $this->planService = new PlanService($apiClient);
        $this->geocodeService = new GeocodeService($apiClient);
        $this->tripService = new TripService($apiClient);
        $this->stopTimesService = new StopTimesService($apiClient);
        $this->stopService = new StopService($apiClient);
        $this->healthService = new HealthService($apiClient);
    }

    /**
     * Formats a coordinate pair (and optional OSM level) as expected by the
     * place parameters of the MOTIS API.
     */
    public static function coordinate(float $latitude, float $longitude, ?float $level = null): string
    {
        return $latitude . ',' . $longitude . ($level === null ? '' : ',' . $level);
    }

    /**
     * @see PlanService::plan()
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
        return $this->planService->plan(
            $fromPlace,
            $toPlace,
            $time,
            $arriveBy,
            $maxTransfers,
            $maxTravelTime,
            $minTransferTime,
            $via,
            $transitModes,
            $extraQuery
        );
    }

    /**
     * @see GeocodeService::geocode()
     *
     * @return list<GeocodeMatch>
     */
    public function geocode(
        string $text,
        array $language = [],
        array $type = [],
        ?string $place = null,
        ?float $placeBias = null,
        ?int $numResults = null,
        array $extraQuery = [],
    ): array {
        return $this->geocodeService->geocode($text, $language, $type, $place, $placeBias, $numResults, $extraQuery);
    }

    /**
     * @see GeocodeService::reverseGeocode()
     *
     * @return list<GeocodeMatch>
     */
    public function reverseGeocode(
        string $place,
        array $type = [],
        ?int $numResults = null,
        array $extraQuery = [],
    ): array {
        return $this->geocodeService->reverseGeocode($place, $type, $numResults, $extraQuery);
    }

    /**
     * @see TripService::trip()
     */
    public function trip(
        string $tripId,
        bool $withScheduledSkippedStops = false,
        ?bool $detailedLegs = null,
        ?bool $joinInterlinedLegs = null,
        array $extraQuery = [],
    ): Itinerary {
        return $this->tripService->trip($tripId, $withScheduledSkippedStops, $detailedLegs, $joinInterlinedLegs, $extraQuery);
    }

    /**
     * @see StopTimesService::stoptimes()
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
        return $this->stopTimesService->stoptimes($stopId, $time, $arriveBy, $both, $n, $pageCursor, $extraQuery);
    }

    /**
     * @see StopService::stop()
     */
    public function stop(
        string $stopId,
        bool $center = false,
        ?float $radius = null,
        ?bool $exactRadius = null,
        array $language = [],
        array $extraQuery = [],
    ): StopInfo200Response {
        return $this->stopService->stop($stopId, $center, $radius, $exactRadius, $language, $extraQuery);
    }

    /**
     * @see HealthService::health()
     */
    public function health(): HealthResponse
    {
        return $this->healthService->health();
    }
}
