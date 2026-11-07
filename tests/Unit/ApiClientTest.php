<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Exception\MotisApiException;
use Sneakyx\MotisPhpClient\Exception\MotisException;
use Sneakyx\MotisPhpClient\Exception\MotisNetworkException;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;
use Sneakyx\MotisPhpClient\Tests\Support\StubClientException;

final class ApiClientTest extends TestCase
{
    public function testGetJsonBuildsRequestWithBaseUrlAndQuery(): void
    {
        $http = new MockHttpClient(new Response(200, ['Content-Type' => 'application/json'], '{"ok": true}'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com/motis/');

        $result = $client->getJson('/api/v1/health', ['a' => 'b c', 'n' => 1]);

        self::assertSame(['ok' => true], $result);

        $request = $http->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('https://example.com/motis/api/v1/health?a=b%20c&n=1', (string) $request->getUri());
        self::assertSame('application/json', $request->getHeaderLine('Accept'));
        self::assertSame('motis-php-client/0.1.0', $request->getHeaderLine('User-Agent'));
    }

    public function testQueryValuesAreSerializedAsMotisExpectsThem(): void
    {
        $http = new MockHttpClient(new Response(200, [], '[]'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        $client->getJson('/x', [
            'skipNull' => null,
            'skipEmptyArray' => [],
            'flag' => true,
            'off' => false,
            'time' => new DateTimeImmutable('2026-11-09T10:00:00+01:00'),
            'via' => ['stopA', 'stopB'],
            'n' => 3.5,
        ]);

        $expected = 'flag=true'
            . '&off=false'
            . '&time=2026-11-09T10%3A00%3A00%2B01%3A00'
            . '&via=stopA'
            . '&via=stopB'
            . '&n=3.5';

        self::assertSame('https://example.com/x?' . $expected, (string) $http->getLastRequest()->getUri());
    }

    public function testApiErrorWithMotisPayload(): void
    {
        $http = new MockHttpClient(new Response(400, [], '{"error": "invalid place"}'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        try {
            $client->getJson('/x');
            self::fail('MotisApiException was expected');
        } catch (MotisApiException $exception) {
            self::assertSame(400, $exception->getStatusCode());
            self::assertSame('invalid place', $exception->getMotisErrorMessage());
            self::assertSame('{"error": "invalid place"}', $exception->getResponseBody());
            self::assertStringContainsString('invalid place', $exception->getMessage());
        }
    }

    public function testApiErrorWithoutMotisErrorPayload(): void
    {
        $http = new MockHttpClient(new Response(502, [], 'Bad Gateway'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        try {
            $client->getJson('/x');
            self::fail('MotisApiException was expected');
        } catch (MotisApiException $exception) {
            self::assertSame(502, $exception->getStatusCode());
            self::assertSame('', $exception->getMotisErrorMessage());
            self::assertStringContainsString('Bad Gateway', $exception->getMessage());
        }
    }

    public function testNetworkFailureIsWrapped(): void
    {
        $http = new MockHttpClient(new StubClientException('Connection refused'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        try {
            $client->getJson('/x');
            self::fail('MotisNetworkException was expected');
        } catch (MotisNetworkException $exception) {
            self::assertInstanceOf(StubClientException::class, $exception->getTransportException());
            self::assertSame('Connection refused', $exception->getTransportException()->getMessage());
            self::assertStringContainsString('https://example.com/x', $exception->getMessage());
        }
    }

    public function testInvalidJsonResponseIsRejected(): void
    {
        $http = new MockHttpClient(new Response(200, [], 'not json'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        $this->expectException(MotisException::class);
        $this->expectExceptionMessage('not valid JSON');

        $client->getJson('/x');
    }

    public function testScalarJsonResponseIsRejected(): void
    {
        $http = new MockHttpClient(new Response(200, [], '"just a string"'));
        $client = new ApiClient($http, new HttpFactory(), 'https://example.com');

        $this->expectException(MotisException::class);
        $this->expectExceptionMessage('not a JSON object or array');

        $client->getJson('/x');
    }
}
