<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Tests\Integration;

use DateTimeInterface;
use Sneakyx\MotisPhpClient\MotisClient;

final class PlanIntegrationTest extends IntegrationTestCase
{
    public function testPlanFindsATransitItineraryBetweenTwoStops(): void
    {
        $client = $this->createClient();
        $from = $client->geocode('Aachen Hbf', type: ['STOP'])[0];
        $to = $client->geocode('Aachen Rothe Erde', type: ['STOP'])[0];

        $response = $client->plan(
            MotisClient::coordinate((float) $from->getLat(), (float) $from->getLon()),
            MotisClient::coordinate((float) $to->getLat(), (float) $to->getLon()),
            $this->testTime()
        );

        $itineraries = $response->getItineraries();
        self::assertNotEmpty($itineraries);

        $first = $itineraries[0];
        self::assertNotNull($first->getDuration());
        self::assertGreaterThan(0, (int) $first->getDuration());
        self::assertNotEmpty($first->getLegs());
        self::assertInstanceOf(DateTimeInterface::class, $first->getStartTime());
        self::assertInstanceOf(DateTimeInterface::class, $first->getEndTime());
    }

    public function testPlanArriveByRespectsTheRequestedArrivalTime(): void
    {
        $client = $this->createClient();
        $from = $client->geocode('Aachen Hbf', type: ['STOP'])[0];
        $to = $client->geocode('Aachen Rothe Erde', type: ['STOP'])[0];
        $arrivalTime = $this->testTime();

        $response = $client->plan(
            MotisClient::coordinate((float) $from->getLat(), (float) $from->getLon()),
            MotisClient::coordinate((float) $to->getLat(), (float) $to->getLon()),
            $arrivalTime,
            arriveBy: true
        );

        $itineraries = $response->getItineraries();
        self::assertNotEmpty($itineraries);

        foreach ($itineraries as $itinerary) {
            self::assertInstanceOf(DateTimeInterface::class, $itinerary->getEndTime());
            self::assertLessThanOrEqual($arrivalTime, $itinerary->getEndTime());
        }
    }
}
