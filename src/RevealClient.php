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
     * @return array<mixed>
     */
    public function listVehicles(string $token): array
    {
        $response = $this->httpClient->request('GET', rtrim($this->config->cmdBaseUrl, '/') . '/vehicles', [
            'Accept' => 'application/json',
            'Authorization' => $this->buildBearerAuthorization($token),
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->decodeJson($response->body, 'vehicles');
    }

    /**
     * Returns the GPS location of the vehicle identified by the vehicle number.
     *
     * @return array<mixed>
     */
    public function getVehicleLocation(string $token, string $vehicleNumber): array
    {
        $vehicleNumber = trim($vehicleNumber);
        if ($vehicleNumber === '') {
            throw new RevealApiException('Vehicle number is required');
        }

        $response = $this->httpClient->request(
            'GET',
            rtrim($this->config->radBaseUrl, '/') . '/vehicles/' . rawurlencode($vehicleNumber) . '/location',
            [
                'Accept' => 'application/json',
                'Authorization' => $this->buildBearerAuthorization($token),
            ]
        );

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->decodeJson($response->body, 'vehicle location');
    }

    /**
     * Returns the GPS history of the vehicle identified by the vehicle number.
     *
     * @return array<mixed>
     */
    public function getVehicleHistory(
        string $token,
        string $vehicleNumber,
        string $startDatetimeUtc,
        string $endDatetimeUtc
    ): array {
        $vehicleNumber = trim($vehicleNumber);
        if ($vehicleNumber === '') {
            throw new RevealApiException('Vehicle number is required');
        }

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

        return $this->decodeJson($response->body, 'vehicle history');
    }

    /**
     * Returns the list of vehicles with active DTCs.
     *
     * @return array<mixed>
     */
    public function getVehiclesActiveDTCS(string $token): array
    {
        $response = $this->httpClient->request('GET', rtrim($this->config->radBaseUrl, '/') . '/vehicles/getvehiclesactivedtcs', [
            'Accept' => 'application/json',
            'Authorization' => $this->buildBearerAuthorization($token),
        ]);

        $this->assertSuccess($response->statusCode, $response->body);

        return $this->decodeJson($response->body, 'vehicles active dtcs');
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
