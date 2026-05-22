<?php

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Http;

interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function request(string $method, string $url, array $headers = []): HttpResponse;
}
