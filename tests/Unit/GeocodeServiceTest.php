<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\GeocodeMatch;
use Sneakyx\MotisPhpClient\Service\GeocodeService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class GeocodeServiceTest extends TestCase
{
    public function testGeocodeBuildsQueryAndParsesMatches(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/geocode_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new GeocodeService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $matches = $service->geocode(
            text: 'Hauptwache',
            language: ['de'],
            type: ['STOP'],
            place: '50.11,8.68',
            numResults: 5,
        );

        self::assertSame(
            'https://example.com/api/v1/geocode?text=Hauptwache&language=de&type=STOP&place=50.11%2C8.68&numResults=5',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertCount(2, $matches);
        self::assertInstanceOf(GeocodeMatch::class, $matches[0]);
        self::assertSame('Frankfurt (Main) Hauptwache', $matches[0]->getName());
        self::assertSame('STOP', $matches[0]->getType());
        self::assertSame(50.11224, $matches[0]->getLat());
        self::assertSame(8.68214, $matches[0]->getLon());
        self::assertSame(0.98, $matches[0]->getScore());
        self::assertSame('ADDRESS', $matches[1]->getType());
        self::assertSame('Zeil', $matches[1]->getStreet());
    }

    public function testReverseGeocodeBuildsQueryAndParsesMatches(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/geocode_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new GeocodeService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $matches = $service->reverseGeocode(place: '50.11224,8.68214', type: ['ADDRESS'], numResults: 3);

        self::assertSame(
            'https://example.com/api/v1/reverse-geocode?place=50.11224%2C8.68214&type=ADDRESS&numResults=3',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertCount(2, $matches);
        self::assertInstanceOf(GeocodeMatch::class, $matches[0]);
    }
}
