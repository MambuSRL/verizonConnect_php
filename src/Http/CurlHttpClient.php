<?php

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Http;

use MambuSRL\VerizonConnect\Exception\RevealApiException;

final class CurlHttpClient implements HttpClientInterface
{
    public function request(string $method, string $url, array $headers = []): HttpResponse
    {
        $ch = curl_init();

        if ($ch === false) {
            throw new RevealApiException('Unable to initialize cURL client');
        }

        $curlHeaders = [];
        foreach ($headers as $name => $value) {
            $curlHeaders[] = sprintf('%s: %s', $name, $value);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $curlHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $body = curl_exec($ch);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RevealApiException(sprintf('HTTP request failed: %s', $error));
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return new HttpResponse($statusCode, $body);
    }
}
