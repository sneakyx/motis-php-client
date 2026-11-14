# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `MotisClient` facade and service layer for the core MOTIS endpoints:
  plan (routing), geocode, reverse-geocode, trip, stoptimes, stop and health (#1)
- Type-safe response models for the full MOTIS v6 API, generated with OpenAPI
  Generator from the official OpenAPI specification (vendored as `openapi.yaml`)
- PSR-18 based HTTP transport (any PSR-18 client, Guzzle by default)
- Error handling: `MotisApiException` (HTTP >= 400) and `MotisNetworkException`
  (transport failures)
- PHPUnit test suite (unit tests, no network required) and GitHub Actions CI
  (PHP 8.1 - 8.4, coverage report)
