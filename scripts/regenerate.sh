#!/usr/bin/env bash
#
# Regenerates the OpenAPI Generator based parts of this package:
# src/Model/*, src/ObjectSerializer.php, src/ApiException.php
#
# Requirements: docker
#
set -euo pipefail

SPEC_URL="${1:-https://raw.githubusercontent.com/motis-project/motis/master/openapi.yaml}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

cd "$ROOT"

echo "Downloading OpenAPI specification from ${SPEC_URL}"
curl -fsSL -o openapi.yaml "$SPEC_URL"

echo "Running OpenAPI Generator"
docker run --rm --user "$(id -u):$(id -g)" \
    -v "$ROOT":/local openapitools/openapi-generator-cli:v7.15.0 generate \
    -i /local/openapi.yaml -g php -o /local/.generated \
    --global-property=apiTests=false,modelTests=false,apiDocs=false,modelDocs=false \
    --additional-properties=invokerPackage=Sneakyx\\MotisPhpClient,srcBasePath=src,composerVendorName=sneakyx,composerProjectName=motis-php-client,artifactVersion=0.1.0

echo "Moving generated files into place"
rm -rf src/Model
cp -r .generated/src/Model src/Model
cp .generated/src/ObjectSerializer.php src/ObjectSerializer.php
cp .generated/src/ApiException.php src/ApiException.php
rm -rf .generated

echo "Applying PHP compatibility renames (match is a reserved keyword in PHP)"
if [ -f src/Model/Match.php ]; then
    mv src/Model/Match.php src/Model/GeocodeMatch.php
    sed -i 's/^class Match /class GeocodeMatch /' src/Model/GeocodeMatch.php
    sed -i "s/openAPIModelName = 'Match'/openAPIModelName = 'GeocodeMatch'/" src/Model/GeocodeMatch.php
    sed -i 's/^ \* Match$/ * GeocodeMatch/' src/Model/GeocodeMatch.php
    sed -i 's/^ \* Match Class Doc Comment$/ * GeocodeMatch Class Doc Comment/' src/Model/GeocodeMatch.php
fi

echo "Done. Run 'composer install && vendor/bin/phpunit' to verify."
