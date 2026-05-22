<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class DriverInfo
{
    public function __construct(
        public readonly ?string $number,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableString($data['Number'] ?? null),
            self::toNullableString($data['FirstName'] ?? null),
            self::toNullableString($data['LastName'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
