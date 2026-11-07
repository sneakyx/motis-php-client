<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Service;

use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\ObjectSerializer;

/**
 * Geocoding: resolves addresses / places to coordinates and vice versa.
 */
final class GeocodeService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * Resolves a (potentially partially typed) address to a list of matches.
     *
     * @param list<string>         $language   language tags (e.g. ["de"])
     * @param list<string>         $type       restrict results to these types (ADDRESS, PLACE, STOP)
     * @param string|null          $place      "latitude,longitude" to bias results towards this coordinate
     * @param float|null           $placeBias  bias strength (higher number = higher bias)
     * @param int|null             $numResults number of suggestions (server default: 10)
     * @param array<string, mixed> $extraQuery any additional geocode endpoint query parameters
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
        $query = \array_merge($extraQuery, [
            'text' => $text,
            'language' => $language,
            'type' => $type,
            'place' => $place,
            'placeBias' => $placeBias,
            'numResults' => $numResults,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v1/geocode', $query),
            GeocodeMatch::class . '[]'
        );
    }

    /**
     * Resolves a coordinate to a list of location guesses.
     *
     * @param list<string>         $type       restrict results to these types (ADDRESS, PLACE, STOP)
     * @param int|null             $numResults number of results (server default: 5)
     * @param array<string, mixed> $extraQuery any additional reverse-geocode endpoint query parameters
     *
     * @return list<GeocodeMatch>
     */
    public function reverseGeocode(
        string $place,
        array $type = [],
        ?int $numResults = null,
        array $extraQuery = [],
    ): array {
        $query = \array_merge($extraQuery, [
            'place' => $place,
            'type' => $type,
            'numResults' => $numResults,
        ]);

        return ObjectSerializer::deserialize(
            $this->apiClient->getJson('/api/v1/reverse-geocode', $query),
            GeocodeMatch::class . '[]'
        );
    }
}
