# AGENTS.md

Guidelines for coding agents working on this repository.

## Project overview

`sneakyx/motis-php-client` is a PHP Composer package for the MOTIS API
(multimodal routing, geocoding, stoptimes). Architecture:

- `src/MotisClient` - facade used by consumers; delegates to the services
- `src/Service` - hand-written services, one per API area (PlanService, GeocodeService, ...)
- `src/Http/ApiClient` - PSR-18 transport: query building, sending, error mapping
- `src/Exception` - package exceptions (MotisException, MotisApiException, MotisNetworkException)
- `src/Model` - GENERATED, type-safe models from the MOTIS OpenAPI spec
- `src/ObjectSerializer` / `src/ApiException` - GENERATED, used for model (de)serialization

## Language

Everything in this repository (code, comments, docs, issues, commits) is **English**.

## Commands

`bash
composer install                          # install dependencies
vendor/bin/phpunit                        # run tests
vendor/bin/phpunit --coverage-text        # tests + coverage (requires pcov or xdebug)
php -d xdebug.mode=coverage vendor/bin/phpunit --coverage-text   # coverage via xdebug
`

## Generated code rules

- Never hand-edit `src/Model`, `src/ObjectSerializer.php` or `src/ApiException.php`.
- Regenerate with `./scripts/regenerate.sh` (requires Docker).
- The OpenAPI schema `Match` maps to the PHP class `Model\GeocodeMatch`
  (`match` is a reserved keyword in PHP); the rename is part of the script.

## Test conventions

- PHPUnit, `tests/Unit`; no network access in tests.
- PSR-18 mock: `tests/Support/MockHttpClient` (replays `Response` objects or
  `ClientExceptionInterface` stubs, records requests).
- JSON fixtures live in `tests/Fixtures` and mirror real MOTIS response payloads.
- Coverage of the hand-written code must stay >= 80 % (generated code is excluded
  in `phpunit.xml`).

## Definition of done

- Test suite green, coverage >= 80 %
- `composer validate` passes
- Documentation updated when behavior changes (README, CHANGELOG)
- Version stays 0.x.x until integration into a real consumer project has been tested
