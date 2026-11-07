<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Unit;

use DateTimeImmutable;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\Http\ApiClient;
use Sneakyx\MotisPhpClient\Model\Plan200Response;
use Sneakyx\MotisPhpClient\Service\PlanService;
use Sneakyx\MotisPhpClient\Tests\Support\MockHttpClient;

final class PlanServiceTest extends TestCase
{
    public function testPlanSendsQueryAndReturnsItineraries(): void
    {
        $fixture = \file_get_contents(__DIR__ . '/../Fixtures/plan_response.json');
        \assert(\is_string($fixture));

        $http = new MockHttpClient(new Response(200, [], $fixture));
        $service = new PlanService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $response = $service->plan(
            fromPlace: '50.1109,8.6821',
            toPlace: 'de:06412:1011',
            time: new DateTimeImmutable('2026-11-09T10:00:00+01:00'),
            arriveBy: false,
            maxTransfers: 2,
            via: ['stopA', 'stopB'],
            transitModes: ['SUBWAY', 'TRAM'],
        );

        self::assertSame(
            'https://example.com/api/v6/plan'
            . '?fromPlace=50.1109%2C8.6821'
            . '&toPlace=de%3A06412%3A1011'
            . '&time=2026-11-09T10%3A00%3A00%2B01%3A00'
            . '&arriveBy=false'
            . '&maxTransfers=2'
            . '&via=stopA'
            . '&via=stopB'
            . '&transitModes=SUBWAY'
            . '&transitModes=TRAM',
            (string) $http->getLastRequest()->getUri()
        );

        self::assertInstanceOf(Plan200Response::class, $response);

        $itineraries = $response->getItineraries();
        self::assertCount(1, $itineraries);
        self::assertSame(480, $itineraries[0]->getDuration());
        self::assertSame(0, $itineraries[0]->getTransfers());
        self::assertSame('5cd2a86f', $itineraries[0]->getId());

        $leg = $itineraries[0]->getLegs()[0];
        self::assertSame('WALK', $leg->getMode());
        self::assertSame('Frankfurt (Main) Hauptbahnhof', $leg->getFrom()->getName());
        self::assertSame(50.1109, $leg->getFrom()->getLat());
        self::assertSame('Frankfurt (Main) Hauptwache', $leg->getTo()->getName());

        self::assertSame('Frankfurt (Main) Hauptbahnhof', $response->getFrom()->getName());
        self::assertSame('Frankfurt (Main) Hauptwache', $response->getTo()->getName());
    }

    public function testExtraQueryParametersArePassedThrough(): void
    {
        $http = new MockHttpClient(new Response(200, [], '[]'));
        $service = new PlanService(new ApiClient($http, new HttpFactory(), 'https://example.com'));

        $service->plan('a', 'b', extraQuery: ['pedestrianProfile' => 'WHEELCHAIR', 'detailedLegs' => true]);

        self::assertSame(
            'https://example.com/api/v6/plan?pedestrianProfile=WHEELCHAIR&detailedLegs=true&fromPlace=a&toPlace=b&arriveBy=false',
            (string) $http->getLastRequest()->getUri()
        );
    }
}
