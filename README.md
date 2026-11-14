# MOTIS PHP Client

A **PHP Composer package** for the [MOTIS API](https://motis-project.de/), enabling multimodal routing, geocoding, stoptimes and trip information in PHP applications. Built with [OpenAPI Generator](https://openapi-generator.tech/), this package provides type-safe models, PSR-18 HTTP client support and a modular service architecture for easy integration with **Laravel**, **Symfony**, **Nextcloud** and any other PHP project.

The default server is the community-run, provider-neutral [Transitous](https://transitous.org) instance at `https://api.transitous.org`. Any self-hosted MOTIS instance works as well.

## Features

- **Type-safe models generated with OpenAPI Generator** from the official MOTIS OpenAPI specification (vendored as `openapi.yaml`)
- **PSR-18 HTTP transport**: works with any PSR-18 HTTP client, defaults to Guzzle
- **Modular architecture**: hand-written service layer (`PlanService`, `GeocodeService`, ...) behind a single `MotisClient` facade
- **Explicit error handling**: `MotisApiException` for HTTP errors (>= 400), `MotisNetworkException` for transport failures
- **Tested**: PHPUnit test suite without network access, coverage of the hand-written code >= 80 %

## Requirements

- PHP >= 8.1
- Composer
- A MOTIS server to talk to, e.g. `https://api.transitous.org` (Transitous) or your own instance

## Installation

`bash
composer require sneakyx/motis-php-client
`

## Quick start

`php
use Sneakyx\MotisPhpClient\MotisClient;
use Sneakyx\MotisPhpClient\Exception\MotisApiException;

$client = new MotisClient('https://api.transitous.org');

try {
    // Route from Frankfurt Central Station to the Hauptwache
    $plan = $client->plan(
        fromPlace: MotisClient::coordinate(50.1109, 8.6821),
        toPlace: 'de:06412:1011',
        maxTransfers: 2,
    );

    foreach ($plan->getItineraries() as $itinerary) {
        echo $itinerary->getDuration() . ' seconds, '
            . $itinerary->getTransfers() . " transfers\n";
    }

    // Geocode a (partial) address
    foreach ($client->geocode('Hauptwache', language: ['de']) as $match) {
        echo $match->getName() . ' @ ' . $match->getLat() . ',' . $match->getLon() . "\n";
    }

    // Departure board for a stop
    $departures = $client->stoptimes('de:06412:1011');
} catch (MotisApiException $e) {
    // HTTP error from the MOTIS server
    echo $e->getStatusCode() . ': ' . $e->getMotisErrorMessage();
}
`

## API coverage

| Endpoint | Facade method | Service |
| --- | --- | --- |
| GET /api/v6/plan | `MotisClient::plan()` | `PlanService` |
| GET /api/v1/geocode | `MotisClient::geocode()` | `GeocodeService` |
| GET /api/v1/reverse-geocode | `MotisClient::reverseGeocode()` | `GeocodeService` |
| GET /api/v6/trip | `MotisClient::trip()` | `TripService` |
| GET /api/v6/stoptimes | `MotisClient::stoptimes()` | `StopTimesService` |
| GET /api/v6/stop | `MotisClient::stop()` | `StopService` |
| GET /api/v1/health | `MotisClient::health()` | `HealthService` |

Every service method accepts an `array $extraQuery` parameter that maps 1:1 to the
remaining query parameters of the MOTIS endpoint, so the full API can be used even
before a parameter gets a dedicated, typed argument.

**Planned** (generated models already ship, service layer still missing):
one-to-many / one-to-all (isochrones), map endpoints, rentals, refresh-itinerary,
realtime trip updates.

## Custom HTTP client / self-hosted MOTIS

`php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Sneakyx\MotisPhpClient\MotisClient;

$client = new MotisClient(
    baseUrl: 'https://motis.example.org',
    httpClient: new Client(['timeout' => 10]),   // any PSR-18 client
    requestFactory: new HttpFactory(),           // any PSR-17 request factory
);
`

## Integration into other projects

The package is a plain Composer library without framework-specific code.

**Laravel** (register a singleton in a service provider):

`php
// app/Providers/AppServiceProvider.php
$this->app->singleton(\Sneakyx\MotisPhpClient\MotisClient::class, function () {
    return new \Sneakyx\MotisPhpClient\MotisClient(config('services.motis.base_url'));
});
`

**Symfony** (`config/services.yaml`):

`yaml
services:
    Sneakyx\MotisPhpClient\MotisClient:
        arguments:
            $baseUrl: '%env(MOTIS_BASE_URL)%'
`

**Nextcloud**: require the package in the app's `composer.json` and use
`MotisClient` in the app's PHP backend. A dedicated Nextcloud app
(motis-nextcloud) that shows public transit connections for calendar events
and contacts on top of this package is planned.

> **Note on versioning:** integration into a real consumer project has not been
> fully tested yet - therefore the package stays at version 0.x.x (pre-1.0)
> until that has been verified.

## Regenerating the models

See [docs/REGENERATION.md](docs/REGENERATION.md). Generated code must never be
edited manually; use `scripts/regenerate.sh`.

## Testing

`bash
composer install
vendor/bin/phpunit                          # plain test run
vendor/bin/phpunit --coverage-text          # with coverage (requires pcov or xdebug)
`

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md). Issues and pull requests in English, please.

## License

MIT - see [LICENSE](LICENSE).
