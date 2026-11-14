# Contributing

Thanks for your interest in contributing to motis-php-client!

## Ground rules

- **Language:** Issues, pull requests, commit messages and documentation are written in **English**.
- **License:** By contributing, you agree that your contributions will be licensed under the MIT license.

## Reporting bugs / requesting features

Open an issue using one of the issue templates. Please include your environment (PHP version,
package version, MOTIS server) in bug reports.

## Development setup

`bash
git clone https://github.com/sneakyx/motis-php-client.git
cd motis-php-client
composer install
vendor/bin/phpunit
`

## Workflow

1. An issue is created and discussed first (duplicate check before opening a new one).
2. Create a feature branch named `feature/<issue-number>-<slug>`.
3. Implement the change **with tests**.
4. Make sure the test suite is green:

   `bash
   vendor/bin/phpunit --coverage-text
   `

   Coverage of the hand-written code (`MotisClient`, `Http/`, `Service/`, `Exception/`)
   must be at least **80 %**.

5. Open a pull request that references the issue (`Closes #123`). The maintainer reviews
   and merges it - do not merge your own pull requests.
6. After the merge: the version is bumped and a tagged release is created.

## Generated code

`src/Model/*` (and `src/ObjectSerializer.php`, `src/ApiException.php`) are generated
with OpenAPI Generator from the vendored `openapi.yaml`. **Never edit these files manually** -
regenerate them instead, see [docs/REGENERATION.md](docs/REGENERATION.md) and
`scripts/regenerate.sh`.

## Coding conventions

- PHP >= 8.1, PSR-12, `declare(strict_types=1)` in every file
- English docblocks, no dead code, no commented-out code
- Unit tests use the PSR-18 mock in `tests/Support/MockHttpClient` and JSON fixtures in `tests/Fixtures`;
  tests must not require network access
