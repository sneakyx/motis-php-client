# Regenerating the OpenAPI-generated code

The following parts of `src/` are generated with
[OpenAPI Generator](https://openapi-generator.tech/) from the official MOTIS
OpenAPI specification (vendored as `openapi.yaml` in the repository root):

- `src/Model/*` - type-safe response/request models for the whole MOTIS API
- `src/ObjectSerializer.php` - (de)serialization helpers for those models
- `src/ApiException.php` - low-level exception used by the serializer

**Never edit these files manually.** To regenerate (requires Docker):

`bash
./scripts/regenerate.sh                              # upstream MOTIS spec
./scripts/regenerate.sh path/to/openapi.yaml        # local spec file
`

The script:

1. Downloads the current `openapi.yaml` from the MOTIS repository
   (unless a local file is passed as the first argument),
2. runs `openapitools/openapi-generator-cli:7.15.0` (Docker) with the
   invoker package `Sneakyx\MotisPhpClient`,
3. moves `src/Model`, `src/ObjectSerializer.php` and `src/ApiException.php`
   into place, and
4. applies PHP-specific compatibility renames: the OpenAPI schema `Match`
   becomes `Model\GeocodeMatch` because `match` is a reserved keyword in PHP.

Afterwards run:

`bash
composer install
vendor/bin/phpunit
`

to verify the package still works. The hand-written parts (`MotisClient`,
`Http/`, `Service/`, `Exception/`) are not touched by regeneration.
