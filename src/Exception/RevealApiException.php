<?php

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Exception;

use RuntimeException;

final class RevealApiException extends RuntimeException
{
    public function __construct(string $message, public readonly int $statusCode = 0)
    {
        parent::__construct($message, $statusCode);
    }
}
