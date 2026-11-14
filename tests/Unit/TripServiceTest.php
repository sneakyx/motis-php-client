<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Itinerary;
use Sneakyx\MotisPhpClient\Service\TripService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class TripServiceTest extends TestCase
{
    public function testTripBuildsQueryAndReturnsItinerary(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/trip_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new TripService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $itinerary = $service->trip(tripId: '1|1234|0|0|0', detailedLegs: false);

        self::assertSame(
            'https://example.com/api/v6/trip?tripId=1%7C1234%7C0%7C0%7C0&withScheduledSkippedStops=false&detailedLegs=false',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertInstanceOf(Itinerary::class, $itinerary);
        self::assertSame(480, $itinerary->getDuration());
        self::assertSame('5cd2a86f', $itinerary->getId());
        self::assertSame('WALK', $itinerary->getLegs()[0]->getMode());
    }
}
