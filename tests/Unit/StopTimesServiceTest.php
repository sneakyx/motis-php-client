<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Stoptimes200Response;
use Sneakyx\MotisPhpClient\Service\StopTimesService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class StopTimesServiceTest extends TestCase
{
    public function testStoptimesBuildsQueryAndReturnsStopTimes(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/stoptimes_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new StopTimesService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $response = $service->stoptimes(
            stopId: 'de:06412:1011',
            time: new DateTimeImmutable('2026-11-09T10:00:00+01:00'),
            n: 5,
        );

        self::assertSame(
            'https://example.com/api/v6/stoptimes?stopId=de%3A06412%3A1011&time=2026-11-09T10%3A00%3A00%2B01%3A00&arriveBy=false&both=false&n=5',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertInstanceOf(Stoptimes200Response::class, $response);
        self::assertSame('Frankfurt (Main) Hauptwache', $response->getPlace()->getName());

        $stopTimes = $response->getStopTimes();
        self::assertCount(1, $stopTimes);
        self::assertSame('U5', $stopTimes[0]->getRouteShortName());
        self::assertSame('Frankfurt (Main) Preungesheim', $stopTimes[0]->getHeadsign());
        self::assertSame('SUBWAY', $stopTimes[0]->getMode());
    }
}
