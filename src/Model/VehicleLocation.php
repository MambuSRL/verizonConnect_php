<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class VehicleLocation
{
    public function __construct(
        public readonly ?InternationalAddress $address,
        public readonly ?float $deltaDistance,
        public readonly ?int $deltaTime,
        public readonly ?int $deviceTimeZoneOffset,
        public readonly ?bool $deviceTimeZoneUseDST,
        public readonly ?string $displayState,
        public readonly ?int $direction,
        public readonly ?string $heading,
        public readonly ?string $driverNumber,
        public readonly ?string $geoFenceName,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?float $speed,
        public readonly ?string $updateUTC,
        public readonly ?bool $isPrivate,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $address = null;
        if (isset($data['Address']) && is_array($data['Address'])) {
            $address = InternationalAddress::fromArray($data['Address']);
        }

        return new self(
            $address,
            self::toNullableFloat($data['DeltaDistance'] ?? null),
            self::toNullableInt($data['DeltaTime'] ?? null),
            self::toNullableInt($data['DeviceTimeZoneOffset'] ?? null),
            self::toNullableBool($data['DeviceTimeZoneUseDST'] ?? null),
            self::toNullableString($data['DisplayState'] ?? null),
            self::toNullableInt($data['Direction'] ?? null),
            self::toNullableString($data['Heading'] ?? null),
            self::toNullableString($data['DriverNumber'] ?? null),
            self::toNullableString($data['GeoFenceName'] ?? null),
            self::toNullableFloat($data['Latitude'] ?? $data['lat'] ?? null),
            self::toNullableFloat($data['Longitude'] ?? $data['lng'] ?? null),
            self::toNullableFloat($data['Speed'] ?? null),
            self::toNullableString($data['UpdateUTC'] ?? null),
            self::toNullableBool($data['IsPrivate'] ?? null),
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

    private static function toNullableInt(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    private static function toNullableFloat(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }
}
