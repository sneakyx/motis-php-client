<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Integration;

use Sneakyx\MotisPhpClient\Model\HealthResponse;

final class HealthIntegrationTest extends IntegrationTestCase
{
    public function testHealthReportsAReachableServer(): void
    {
        $health = $this->createClient()->health();

        self::assertInstanceOf(HealthResponse::class, $health);
        self::assertNotNull($health->getRt());
    }
}
