<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisPhpClient\Model\Plan200Response;
use Sneakyx\MotisPhpClient\Model\StopInfo200Response;
use Sneakyx\MotisPhpClient\Model\Stoptimes200Response;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class MotisClientTest extends TestCase
{
    public function testCoordinateFormatting(): void
    {
        self::assertSame('50.1109,8.6821', MotisClient::coordinate(50.1109, 8.6821));
        self::assertSame('50.1109,8.6821,2', MotisClient::coordinate(50.1109, 8.6821, 2.0));
    }

    public function testUsesDefaultBaseUrl(): void
    {
        $http = new MockHttpClient(new Response(200, [], '{"rt": true}'));
        $client = new MotisClient(httpClient: $http, requestFactory: new HttpFactory());

        $health = $client->health();

        self::assertInstanceOf(HealthResponse::class, $health);
        self::assertSame('https://api.transitous.org/api/v1/health', (string) $http->getLastRequest()->getUri());
    }

    public function testFacadeDelegatesToPlanService(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/plan_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $client = new MotisClient('https://example.com', $http, new HttpFactory());

        $response = $client->plan('50.1109,8.6821', 'de:06412:1011');

        self::assertInstanceOf(Plan200Response::class, $response);
        self::assertSame(
            'https://example.com/api/v6/plan?fromPlace=50.1109%2C8.6821&toPlace=de%3A06412%3A1011&arriveBy=false',
            (string) $http->getLastRequest()->getUri()
        );
    }

    public function testFacadeDelegatesToGeocodeService(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/geocode_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $client = new MotisClient('https://example.com', $http, new HttpFactory());

        $matches = $client->geocode('Hauptwache');

        self::assertContainsOnlyInstancesOf(GeocodeMatch::class, $matches);
        self::assertSame(
            'https://example.com/api/v1/geocode?text=Hauptwache',
            (string) $http->getLastRequest()->getUri()
        );
    }

    public function testFacadeDelegatesToTripStoptimesStopAndReverseGeocode(): void
    {
        $trip = \file_get_contents(__DIR__ . '/../Fixtures/trip_response.json');
        $stoptimes = \file_get_contents(__DIR__ . '/../Fixtures/stoptimes_response.json');
        $stop = \file_get_contents(__DIR__ . '/../Fixtures/stop_response.json');
        $geocode = \file_get_contents(__DIR__ . '/../Fixtures/geocode_response.json');
        \assert(\is_string($trip));
        \assert(\is_string($stoptimes));
        \assert(\is_string($stop));
        \assert(\is_string($geocode));

        $http = new MockHttpClient(
            new Response(200, [], $trip),
            new Response(200, [], $stoptimes),
            new Response(200, [], $stop),
            new Response(200, [], $geocode),
        );
        $client = new MotisClient('https://example.com', $http, new HttpFactory());

        self::assertInstanceOf(Itinerary::class, $client->trip('1|1234|0|0|0'));
        self::assertStringContainsString('/api/v6/trip?', (string) $http->getRequests()[0]->getUri());

        self::assertInstanceOf(Stoptimes200Response::class, $client->stoptimes('de:06412:1011'));
        self::assertStringContainsString('/api/v6/stoptimes?', (string) $http->getRequests()[1]->getUri());

        self::assertInstanceOf(StopInfo200Response::class, $client->stop('de:06412:1011'));
        self::assertStringContainsString('/api/v6/stop?', (string) $http->getRequests()[2]->getUri());

        self::assertCount(2, $client->reverseGeocode('50.11224,8.68214'));
        self::assertStringContainsString('/api/v1/reverse-geocode?', (string) $http->getRequests()[3]->getUri());
    }
}
