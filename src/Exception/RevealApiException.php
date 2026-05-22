<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Exception;

use RuntimeException;

final class RevealApiException extends RuntimeException
{
    /**
        * Creates an application exception with an optional HTTP status code.
     */
    public function __construct(string $message, public readonly int $statusCode = 0)
    {
        parent::__construct($message, $statusCode);
    }
}
