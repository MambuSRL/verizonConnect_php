<?php

declare(strict_types=1);

namespace MambuSRL\VerizonConnect;

use JsonException;
use MambuSRL\VerizonConnect\Exception\RevealApiException;
use MambuSRL\VerizonConnect\Http\CurlHttpClient;
use MambuSRL\VerizonConnect\Http\HttpClientInterface;

final class RevealClient
{
    public function __construct(
        private readonly RevealConfig $config,
        private readonly HttpClientInterface $httpClient = new CurlHttpClient()
    ) {
    }

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

    private function buildBearerAuthorization(string $token): string
    {
        $token = trim($token);
        if ($token === '') {
            throw new RevealApiException('Token is required');
        }

        return sprintf('Atmosphere atmosphere_app_id=%s, Bearer %s', $this->config->appId, $token);
    }

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
