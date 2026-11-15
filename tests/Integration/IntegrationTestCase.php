<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sneakyx\MotisPhpClient\MotisClient;

/**
 * Base class for all integration tests.
 *
 * The suite is skipped unless the MOTIS_BASE_URL environment variable points
 * to a running MOTIS server, so offline runs stay fast. CI starts a local
 * MOTIS server with the pinned Aachen test dataset (see
 * .github/workflows/integration.yml). The GTFS feed of that dataset is valid
 * from 2025-12-08 until 2026-12-12, so all tests use a fixed time inside
 * that window; when the pinned dataset is refreshed, TEST_TIME has to be
 * adjusted accordingly.
 */
abstract class IntegrationTestCase extends TestCase
{
    /**
     * Fixed departure/arrival time inside the GTFS validity window of the
     * pinned test dataset (a Wednesday, 10:00 local time in Aachen).
     */
    protected const TEST_TIME = '2026-11-18T10:00:00+01:00';

    public static function setUpBeforeClass(): void
    {
        $baseUrl = \getenv('MOTIS_BASE_URL');
        if ($baseUrl === false || $baseUrl === '') {
            self::markTestSkipped(
                'MOTIS_BASE_URL is not set; point it at a running MOTIS server to run the integration tests.'
            );
        }
    }

    protected function createClient(): MotisClient
    {
        /** @var string $baseUrl */
        $baseUrl = \getenv('MOTIS_BASE_URL');

        return new MotisClient($baseUrl);
    }

    /**
     * Returns the pinned departure/arrival time used by all tests.
     */
    protected function testTime(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::TEST_TIME);
    }
}
