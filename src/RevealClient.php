<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect;

use JsonException;
use MambuSRL\VerizonConnect\Exception\RevealApiException;
use MambuSRL\VerizonConnect\Http\CurlHttpClient;
use MambuSRL\VerizonConnect\Http\HttpClientInterface;
use MambuSRL\VerizonConnect\Model\ContentResourceByVehicleNumberVehicleStatus;
use MambuSRL\VerizonConnect\Model\GpsHistoryByVehicleNumberResponse;
use MambuSRL\VerizonConnect\Model\Location;
use MambuSRL\VerizonConnect\Model\VehicleDTCHistory;
use MambuSRL\VerizonConnect\Model\VehicleECMStatus;
use MambuSRL\VerizonConnect\Model\Vehicle;
use MambuSRL\VerizonConnect\Model\VehicleLocation;
use MambuSRL\VerizonConnect\Model\VehicleStatus;
use MambuSRL\VerizonConnect\Model\VehiclesActiveDTC;

final class RevealClient
{
    /**
        * Creates the client with the VerizonConnect configuration and HTTP implementation to use.
     */
    public function __construct(
        private readonly RevealConfig $config,
        private readonly HttpClientInterface $httpClient = new CurlHttpClient()
    ) {
    }

    /**
        * Retrieves the authentication token from the VerizonConnect service.
     */
    public function getToken(): string
    {
        $authorization = base64_encode($this->config->username . ':' . $this->config->password);
        if ($authorization === false) {
            throw new RevealApiException('Unable to encode credentials');
        }

        $response = $this->httpClient->request('GET', $this->config->tokenUrl, [
            'Accept' => 'text/plain',
            'Content-Type' => 'text/plain',
            'Authorization' => 'Basic ' . $authorization,
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        $token = trim($response->body);
        if ($token === '') {
            throw new RevealApiException('Token is empty', $response->statusCode);
        }

        return $token;
    }

    /**
     * Returns the list of vehicles available for the given token.
     *
     * @return array<int, Vehicle>
     */
    public function listVehicles(string $token): array
    {
        $response = $this->httpClient->request('GET', rtrim($this->config->cmdBaseUrl, '/') . '/vehicles', [
            'Accept' => 'application/json',
            'Authorization' => $this->buildBearerAuthorization($token),
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        $payload = $this->decodeJson($response->body, 'vehicles');
        if (!array_is_list($payload)) {
            $items = $payload['items'] ?? null;
            if (!is_array($items)) {
                throw new RevealApiException('Unexpected vehicles response format');
            }

            $payload = $items;
        }

        return $this->mapList(
            $payload,
            static fn (array $item): Vehicle => Vehicle::fromArray($item),
            'vehicles'
        );
    }

    /**
     * Returns the GPS location of the vehicle identified by the vehicle number.
     *
     * @return VehicleLocation
     */
    public function getVehicleLocation(string $token, string $vehicleNumber): VehicleLocation
    {
        $vehicleNumber = $this->normalizeVehicleNumber($vehicleNumber);

        $response = $this->httpClient->request(
            'GET',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/' . rawurlencode($vehicleNumber) . '/location',
            [
                'Accept' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ]
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return VehicleLocation::fromArray(
            $this->expectObjectPayload(
                $this->decodeJson($response->body, 'vehicle location'),
                'vehicle location'
            )
        );
    }

    /**
     * Returns the GPS history of the vehicle identified by the vehicle number.
     *
    * @return array<int, GpsHistoryByVehicleNumberResponse>
     */
    public function getVehicleHistory(
        string $token,
        string $vehicleNumber,
        string $startDatetimeUtc,
        string $endDatetimeUtc
    ): array {
        $vehicleNumber = $this->normalizeVehicleNumber($vehicleNumber);

        $startDatetimeUtc = trim($startDatetimeUtc);
        if ($startDatetimeUtc === '') {
            throw new RevealApiException('Start datetime UTC is required');
        }

        $endDatetimeUtc = trim($endDatetimeUtc);
        if ($endDatetimeUtc === '') {
            throw new RevealApiException('End datetime UTC is required');
        }

        $url = rtrim($this->config->radBaseUrl, '/')
            . '/vehicles/'
            . rawurlencode($vehicleNumber)
            . '/status/history?startdatetimeutc='
            . rawurlencode($startDatetimeUtc)
            . '&enddatetimeutc='
            . rawurlencode($endDatetimeUtc);

        $response = $this->httpClient->request('GET', $url, [
            'Accept' => 'application/json',
            'Authorization' => $this->buildBearerAuthorization($token),
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->mapList(
            $this->decodeJson($response->body, 'vehicle history'),
            static fn (array $item): GpsHistoryByVehicleNumberResponse => GpsHistoryByVehicleNumberResponse::fromArray($item),
            'vehicle history'
        );
    }

    /**
     * Returns the list of vehicles with active DTCs.
     *
    * @return array<int, VehiclesActiveDTC>
     */
    public function getVehiclesActiveDTCS(string $token): array
    {
        $response = $this->httpClient->request('GET', rtrim($this->config->radBaseUrl, '/') . '/vehicles/getvehiclesactivedtcs', [
            'Accept' => 'application/json',
            'Authorization' => $this->buildBearerAuthorization($token),
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->mapList(
            $this->decodeJson($response->body, 'vehicles active dtcs'),
            static fn (array $item): VehiclesActiveDTC => VehiclesActiveDTC::fromArray($item),
            'vehicles active dtcs'
        );
    }

    /**
     * Returns the current locations for the provided vehicle numbers.
     *
     * @param array<int, string> $vehicleNumbers
        * @return array<int, Location>
     */
    public function getVehiclesLocations(string $token, array $vehicleNumbers): array
    {
        $requestBody = $this->encodeJson(
            $this->normalizeVehicleNumbers($vehicleNumbers),
            'vehicles locations request'
        );

        $response = $this->httpClient->request(
            'POST',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/locations',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ],
            $requestBody
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->mapList(
            $this->decodeJson($response->body, 'vehicles locations'),
            fn (array $item): Location => Location::fromVehicleLocationArray($this->extractBulkVehicleLocationValue($item)),
            'vehicles locations'
        );
    }

    /**
     * Returns the current statuses for the provided vehicle numbers.
     *
     * @param array<int, string> $vehicleNumbers
    * @return array<int, ContentResourceByVehicleNumberVehicleStatus>
     */
    public function getVehiclesStatuses(string $token, array $vehicleNumbers): array
    {
        $requestBody = $this->encodeJson(
            $this->normalizeVehicleNumbers($vehicleNumbers),
            'vehicles statuses request'
        );

        $response = $this->httpClient->request(
            'POST',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/statuses',
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ],
            $requestBody
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->mapList(
            $this->decodeJson($response->body, 'vehicles statuses'),
            static fn (array $item): ContentResourceByVehicleNumberVehicleStatus => ContentResourceByVehicleNumberVehicleStatus::fromArray($item),
            'vehicles statuses'
        );
    }

    /**
     * Returns ECM status details for the specified vehicle.
     *
    * @return VehicleECMStatus
     */
    public function getVehicleEcmStatus(string $token, string $vehicleNumber): VehicleECMStatus
    {
        $vehicleNumber = $this->normalizeVehicleNumber($vehicleNumber);

        $response = $this->httpClient->request(
            'GET',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/' . rawurlencode($vehicleNumber) . '/getecmstatusbyvehiclenumber',
            [
                'Accept' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ]
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return VehicleECMStatus::fromArray(
            $this->expectObjectPayload(
                $this->decodeJson($response->body, 'vehicle ecm status'),
                'vehicle ecm status'
            )
        );
    }

    /**
     * Returns DTC history for the specified vehicle.
     *
    * @return VehicleDTCHistory
     */
    public function getVehicleDtcHistory(string $token, string $vehicleNumber): VehicleDTCHistory
    {
        $vehicleNumber = $this->normalizeVehicleNumber($vehicleNumber);

        $response = $this->httpClient->request(
            'GET',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/' . rawurlencode($vehicleNumber) . '/getdtchistorybyvehiclenumber',
            [
                'Accept' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ]
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return VehicleDTCHistory::fromArray(
            $this->expectObjectPayload(
                $this->decodeJson($response->body, 'vehicle dtc history'),
                'vehicle dtc history'
            )
        );
    }

    /**
     * Returns status information for the specified vehicle.
     *
    * @return VehicleStatus
     */
    public function getVehicleStatus(string $token, string $vehicleNumber): VehicleStatus
    {
        $vehicleNumber = $this->normalizeVehicleNumber($vehicleNumber);

        $response = $this->httpClient->request(
            'GET',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/' . rawurlencode($vehicleNumber) . '/status',
            [
                'Accept' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ]
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return VehicleStatus::fromArray(
            $this->expectObjectPayload(
                $this->decodeJson($response->body, 'vehicle status'),
                'vehicle status'
            )
        );
    }

    /**
     * @template T
     * @param array<mixed> $payload
     * @param callable(array<mixed>): T $mapper
     * @return array<int, T>
     */
    private function mapList(array $payload, callable $mapper, string $context): array
    {
        if (!array_is_list($payload)) {
            throw new RevealApiException(sprintf('Unexpected %s response format', $context));
        }

        $mapped = [];
        foreach ($payload as $item) {
            if (!is_array($item)) {
                throw new RevealApiException(sprintf('Unexpected %s response format', $context));
            }

            $mapped[] = $mapper($item);
        }

        return $mapped;
    }

    /**
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    private function expectObjectPayload(array $payload, string $context): array
    {
        if (array_is_list($payload)) {
            throw new RevealApiException(sprintf('Unexpected %s response format', $context));
        }

        return $payload;
    }

    /**
     * @param array<mixed> $payload
     * @return array<mixed>
     */
    private function extractBulkVehicleLocationValue(array $payload): array
    {
        $contentResource = $payload['ContentResource'] ?? null;
        if (!is_array($contentResource)) {
            throw new RevealApiException('Unexpected vehicles locations response format');
        }

        $value = $contentResource['Value'] ?? null;
        if (!is_array($value)) {
            throw new RevealApiException('Unexpected vehicles locations response format');
        }

        return $value;
    }

    /**
        * Builds the Bearer authorization header required by the VerizonConnect APIs.
     */
    private function buildBearerAuthorization(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            throw new RevealApiException('Token is required');
        }

        return sprintf('Atmosphere atmosphere_app_id=%s, Bearer %s', $this->config->appId, $token);
    }

    /**
        * Verifies that the HTTP response completed successfully.
     */
    private function assertSuccess(int $statusCode, string $body): void
    {
        if ($statusCode < 200 || $statusCode > 299) {
            throw new RevealApiException(
                sprintf('Reveal API request failed with status %d: %s', $statusCode, $body),
                $statusCode
            );
        }
    }

    /**
     * Validates and normalizes a single vehicle number.
     */
    private function normalizeVehicleNumber(string $vehicleNumber): string
    {
        $vehicleNumber = trim($vehicleNumber);
        if ($vehicleNumber === '') {
            throw new RevealApiException('Vehicle number is required');
        }

        return $vehicleNumber;
    }

    /**
     * Validates and normalizes a list of vehicle numbers.
     *
     * @param array<int, string> $vehicleNumbers
     * @return array<int, string>
     */
    private function normalizeVehicleNumbers(array $vehicleNumbers): array
    {
        if ($vehicleNumbers === []) {
            throw new RevealApiException('At least one vehicle number is required');
        }

        $normalizedVehicleNumbers = [];
        foreach ($vehicleNumbers as $vehicleNumber) {
            if (!is_string($vehicleNumber)) {
                throw new RevealApiException('Vehicle numbers must be strings');
            }

            $normalizedVehicleNumbers[] = $this->normalizeVehicleNumber($vehicleNumber);
        }

        return $normalizedVehicleNumbers;
    }

    /**
     * Encodes data to JSON and wraps encoding errors in domain exceptions.
     */
    private function encodeJson(array $data, string $context): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RevealApiException(
                sprintf('Unable to encode %s: %s', $context, $exception->getMessage())
            );
        }
    }

    /**
     * Decodes a JSON response and validates that the format is an array.
     *
     * @return array<mixed>
     */
    private function decodeJson(string $body, string $context): array
    {
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RevealApiException(
                sprintf('Unable to decode %s response: %s', $context, $exception->getMessage())
            );
        }

        if (!is_array($decoded)) {
            throw new RevealApiException(sprintf('Unexpected %s response format', $context));
        }

        return $decoded;
    }
}
