<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class VehiclesActiveDTC
{
    public function __construct(
        public readonly ?string $vehicleNumber,
        public readonly ?string $vehicleName,
        public readonly ?string $activeDTCs,
        public readonly ?string $lastUpdatedDateTime,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableString($data['VehicleNumber'] ?? null),
            self::toNullableString($data['VehicleName'] ?? null),
            self::toNullableString($data['ActiveDTCs'] ?? null),
            self::toNullableString($data['LastUpdatedDateTime'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
