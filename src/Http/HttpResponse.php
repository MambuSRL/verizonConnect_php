<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Http;

final class HttpResponse
{
    /**
        * Stores the HTTP response status code and body.
     */
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body
    ) {
    }
}
