<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\StopInfo200Response;
use Sneakyx\MotisPhpClient\Service\StopService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class StopServiceTest extends TestCase
{
    public function testStopBuildsQueryAndReturnsPlace(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/stop_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new StopService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $response = $service->stop(stopId: 'de:06412:1011', language: ['de']);

        self::assertSame(
            'https://example.com/api/v6/stop?stopId=de%3A06412%3A1011&center=false&language=de',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertInstanceOf(StopInfo200Response::class, $response);
        self::assertSame('Frankfurt (Main) Hauptwache', $response->getPlace()->getName());
        self::assertSame('de:06412:1011', $response->getPlace()->getStopId());
        self::assertSame(50.11224, $response->getPlace()->getLat());
    }
}
