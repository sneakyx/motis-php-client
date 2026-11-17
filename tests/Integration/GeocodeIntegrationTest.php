<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Integration;

final class GeocodeIntegrationTest extends IntegrationTestCase
{
    public function testGeocodeFindsAachenCentralStationAsStop(): void
    {
        $matches = $this->createClient()->geocode('Aachen Hbf', type: ['STOP']);

        self::assertNotEmpty($matches);
        $first = $matches[0];
        self::assertSame('STOP', $first->getType());
        self::assertStringContainsString('Aachen', (string) $first->getName());
        self::assertNotNull($first->getLat());
        self::assertNotNull($first->getLon());
        self::assertGreaterThan(50.7, $first->getLat());
        self::assertLessThan(50.85, $first->getLat());
        self::assertGreaterThan(6.0, $first->getLon());
        self::assertLessThan(6.2, $first->getLon());
    }

    public function testGeocodeFindsAnAddressInsideAachen(): void
    {
        $matches = $this->createClient()->geocode('Karolinenstraße 10, Aachen');

        self::assertNotEmpty($matches);
        $first = $matches[0];
        self::assertNotNull($first->getLat());
        self::assertNotNull($first->getLon());
        self::assertGreaterThan(50.7, $first->getLat());
        self::assertLessThan(50.85, $first->getLat());
        self::assertGreaterThan(6.0, $first->getLon());
        self::assertLessThan(6.2, $first->getLon());
    }
}
