<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class VehicleDTCHistoryDetails
{
    public function __construct(
        public readonly ?string $dtc,
        public readonly ?bool $isActive,
        public readonly ?string $lastUpdatedDateTime,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableString($data['DTC'] ?? null),
            self::toNullableBool($data['IsActive'] ?? null),
            self::toNullableString($data['LastUpdatedDateTime'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function toNullableBool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }
}
