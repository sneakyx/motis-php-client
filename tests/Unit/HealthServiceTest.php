<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\HealthResponse;
use Sneakyx\MotisPhpClient\Service\HealthService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class HealthServiceTest extends TestCase
{
    public function testHealthCallsHealthEndpoint(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/health_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new HealthService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $health = $service->health();

        self::assertSame('https://example.com/api/v1/health', (string) $http->getLastRequest()->getUri());

        self::assertInstanceOf(HealthResponse::class, $health);
        self::assertTrue($health->getRt());
        self::assertFalse($health->getGbfs());
    }
}
