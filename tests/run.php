<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

require_once __DIR__ . '/../src/RevealConfig.php';
require_once __DIR__ . '/../src/Exception/RevealApiException.php';
require_once __DIR__ . '/../src/Http/HttpResponse.php';
require_once __DIR__ . '/../src/Http/HttpClientInterface.php';
require_once __DIR__ . '/../src/Http/CurlHttpClient.php';
require_once __DIR__ . '/../src/RevealClient.php';

use MambuSRL\VerizonConnect\Exception\RevealApiException;
use MambuSRL\VerizonConnect\Http\HttpClientInterface;
use MambuSRL\VerizonConnect\Http\HttpResponse;
use MambuSRL\VerizonConnect\RevealClient;
use MambuSRL\VerizonConnect\RevealConfig;

final class FakeHttpClient implements HttpClientInterface
{
    /**
     * @var array<int, array{method: string, url: string, headers: array<string, string>}>
     */
    public array $requests = [];

    /**
     * @param list<HttpResponse> $responses
     */
    /**
        * Initializes the fake client with the responses to return in sequence.
     */
    public function __construct(private array $responses)
    {
    }

    /**
        * Records the received request and returns the next fake response.
     */
    public function request(string $method, string $url, array $headers = []): HttpResponse
    {
        $this->requests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
        ];

        return array_shift($this->responses) ?? new HttpResponse(500, 'No fake response configured');
    }
}

/**
 * Runs the library test suite.
 *
 * @return list<array{0: string, 1: Closure(): void}>
 */
function tests(): array
{
    return [
        ['get token uses basic authorization and trims response', function (): void {
            $httpClient = new FakeHttpClient([new HttpResponse(200, "  token-value  ")]);
            $client = new RevealClient(config(), $httpClient);

            $token = $client->getToken();

            assertSame('token-value', $token);
            assertSame('GET', $httpClient->requests[0]['method']);
            assertSame('https://fim.api.eu.fleetmatics.com/token', $httpClient->requests[0]['url']);
            assertSame('text/plain', $httpClient->requests[0]['headers']['Accept']);
            assertSame('text/plain', $httpClient->requests[0]['headers']['Content-Type']);
            assertTrue(str_starts_with($httpClient->requests[0]['headers']['Authorization'], 'Basic '));
        }],
        ['list vehicles uses cmd endpoint and atmosphere header', function (): void {
            $httpClient = new FakeHttpClient([new HttpResponse(200, '{"items":[{"id":1}]}')]);
            $client = new RevealClient(config(), $httpClient);

            $vehicles = $client->listVehicles('token-123');

            assertSame(['items' => [['id' => 1]]], $vehicles);
            assertSame('https://fim.api.eu.fleetmatics.com/cmd/v1/vehicles', $httpClient->requests[0]['url']);
            assertSame(
                'Atmosphere atmosphere_app_id=app-id, Bearer token-123',
                $httpClient->requests[0]['headers']['Authorization']
            );
        }],
        ['vehicle location uses rad endpoint and encodes vehicle number', function (): void {
            $httpClient = new FakeHttpClient([new HttpResponse(200, '{"lat":1.1,"lng":2.2}')]);
            $client = new RevealClient(config(), $httpClient);

            $location = $client->getVehicleLocation('token-123', 'VH 1/2');

            assertSame(['lat' => 1.1, 'lng' => 2.2], $location);
            assertSame('https://fim.api.eu.fleetmatics.com/rad/v1/vehicles/VH%201%2F2/location', $httpClient->requests[0]['url']);
        }],
        ['vehicle history uses history endpoint with query parameters', function (): void {
            $httpClient = new FakeHttpClient([new HttpResponse(200, '{"items":[{"id":1}]}')]);
            $client = new RevealClient(config(), $httpClient);

            $history = $client->getVehicleHistory(
                'token-123',
                'VH 1/2',
                '2026-05-22T00:00:00Z',
                '2026-05-22T23:59:59Z'
            );

            assertSame(['items' => [['id' => 1]]], $history);
            assertSame(
                'https://fim.api.eu.fleetmatics.com/rad/v1/vehicles/VH%201%2F2/status/history?startdatetimeutc=2026-05-22T00%3A00%3A00Z&enddatetimeutc=2026-05-22T23%3A59%3A59Z',
                $httpClient->requests[0]['url']
            );
            assertSame(
                'Atmosphere atmosphere_app_id=app-id, Bearer token-123',
                $httpClient->requests[0]['headers']['Authorization']
            );
        }],
        ['active dtcs uses dedicated endpoint and authorization header', function (): void {
            $httpClient = new FakeHttpClient([new HttpResponse(200, '[{"VehicleNumber":"VH1","ActiveDTCs":"P0001"}]')]);
            $client = new RevealClient(config(), $httpClient);

            $activeDtcs = $client->getVehiclesActiveDTCS('token-123');

            assertSame([['VehicleNumber' => 'VH1', 'ActiveDTCs' => 'P0001']], $activeDtcs);
            assertSame('https://fim.api.eu.fleetmatics.com/rad/v1/vehicles/getvehiclesactivedtcs', $httpClient->requests[0]['url']);
            assertSame(
                'Atmosphere atmosphere_app_id=app-id, Bearer token-123',
                $httpClient->requests[0]['headers']['Authorization']
            );
        }],
        ['empty vehicle number is rejected', function (): void {
            $httpClient = new FakeHttpClient([]);
            $client = new RevealClient(config(), $httpClient);

            try {
                $client->getVehicleLocation('token-123', '   ');
                throw new RuntimeException('Expected exception was not thrown.');
            } catch (RevealApiException $exception) {
                assertSame('Vehicle number is required', $exception->getMessage());
            }
        }],
    ];
}

/**
 * Builds a test configuration with consistent fake values.
 */
function config(): RevealConfig
{
    return new RevealConfig(
        appId: 'app-id',
        username: 'user',
        password: 'pass'
    );
}

/**
 * Verifies that two values are identical.
 */
function assertSame(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException('Assertion failed. Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

/**
 * Verifies that a boolean value is true.
 */
function assertTrue(bool $value): void
{
    if ($value !== true) {
        throw new RuntimeException('Assertion failed. Value is not true.');
    }
}

$failures = [];

foreach (tests() as [$name, $test]) {
    try {
        $test();
        echo "[OK] {$name}\n";
    } catch (Throwable $throwable) {
        $failures[] = sprintf('[FAIL] %s: %s', $name, $throwable->getMessage());
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }

    exit(1);
}
