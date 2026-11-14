<?php

declare(strict_types=1);

namespace Sneakyx\MotisPhpClient\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Sneakyx\MotisPhpClient\Exception\MotisApiException;
use Sneakyx\MotisPhpClient\Exception\MotisException;
use Sneakyx\MotisPhpClient\Exception\MotisNetworkException;

/**
 * Minimal PSR-18 based transport for the MOTIS REST API.
 *
 * Builds GET requests, sends them through any PSR-18 HTTP client and maps
 * transport level errors and HTTP error statuses to package exceptions.
 */
final class ApiClient
{
    private const USER_AGENT = 'motis-php-client/0.1.0';

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * Performs a GET request and returns the decoded JSON payload.
     *
     * @param array<string, mixed> $query Query parameters:
     *                                    - null values and empty arrays are omitted
     *                                    - booleans are serialized as "true"/"false"
     *                                    - DateTimeInterface values use RFC 3339
     *                                    - arrays are repeated once per value
     *
     * @return array<int|string, mixed>
     *
     * @throws MotisApiException     if the server responds with HTTP >= 400
     * @throws MotisNetworkException if the transport fails before a response arrives
     * @throws MotisException        if the response is not valid JSON
     */
    public function getJson(string $path, array $query = []): array
    {
        $target = $this->buildTarget($path, $query);

        $request = $this->requestFactory
            ->createRequest('GET', $target)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', self::USER_AGENT);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            throw new MotisNetworkException($target, $exception);
        }

        $body = (string) $response->getBody();

        if ($response->getStatusCode() >= 400) {
            throw self::createApiException($response->getStatusCode(), $body);
        }

        return self::decodeJsonBody($body);
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildTarget(string $path, array $query): string
    {
        $target = \rtrim($this->baseUrl, '/') . $path;
        $queryString = $this->buildQueryString($query);

        if ($queryString === '') {
            return $target;
        }

        return $target . '?' . $queryString;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function buildQueryString(array $query): string
    {
        $parts = [];

        foreach ($query as $name => $value) {
            $values = self::flattenValue($value);

            if ($values === []) {
                continue;
            }

            $name = \rawurlencode((string) $name);

            foreach ($values as $item) {
                $parts[] = $name . '=' . \rawurlencode($item);
            }
        }

        return \implode('&', $parts);
    }

    /**
     * @return list<string>
     */
    private static function flattenValue(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if (\is_array($value)) {
            $items = [];

            foreach ($value as $item) {
                foreach (self::flattenValue($item) as $flat) {
                    $items[] = $flat;
                }
            }

            return $items;
        }

        if ($value instanceof \DateTimeInterface) {
            return [$value->format(\DateTimeInterface::ATOM)];
        }

        if (\is_bool($value)) {
            return [$value ? 'true' : 'false'];
        }

        return [(string) $value];
    }

    private static function createApiException(int $statusCode, string $body): MotisApiException
    {
        $motisErrorMessage = '';
        $decoded = \json_decode($body, true);

        if (\is_array($decoded) && isset($decoded['error']) && \is_string($decoded['error'])) {
            $motisErrorMessage = $decoded['error'];
        }

        return new MotisApiException($motisErrorMessage, $statusCode, $body);
    }

    /**
     * @return array<int|string, mixed>
     */
    private static function decodeJsonBody(string $body): array
    {
        try {
            $decoded = \json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new MotisException(
                \sprintf('MOTIS response is not valid JSON: %s', $exception->getMessage()),
                0,
                $exception
            );
        }

        if (!\is_array($decoded)) {
            throw new MotisException('MOTIS response is not a JSON object or array');
        }

        return $decoded;
    }
}
