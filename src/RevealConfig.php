<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect;

final class RevealConfig
{
    public const RAD_API_BASE_URL = 'https://fim.api.eu.fleetmatics.com/rad/v1/';

    /**
        * Initializes the connection parameters and VerizonConnect endpoints.
     */
    public function __construct(
        public readonly string $appId,
        public readonly string $username,
        public readonly string $password,
        public readonly string $tokenUrl = 'https://fim.api.eu.fleetmatics.com/token',
        public readonly string $cmdBaseUrl = 'https://fim.api.eu.fleetmatics.com/cmd/v1',
        public readonly string $radBaseUrl = self::RAD_API_BASE_URL
    ) {
    }
}
