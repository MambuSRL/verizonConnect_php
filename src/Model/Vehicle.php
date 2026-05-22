<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class Vehicle
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?string $vehicleNumber,
        public readonly ?string $registrationNumber,
        public readonly ?string $vin,
        public readonly ?string $make,
        public readonly ?int $year,
        public readonly ?string $model,
        public readonly ?float $tankCapacity,
        public readonly ?float $highwayMPG,
        public readonly ?float $cityMPG,
        public readonly ?int $fuelType,
        public readonly ?int $vehicleSize,
        public readonly ?bool $hasNavigationDevice,
        public readonly ?bool $hasTachograph,
        public readonly ?int $vehicleId,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableString($data['Name'] ?? null),
            self::toNullableString($data['VehicleNumber'] ?? null),
            self::toNullableString($data['RegistrationNumber'] ?? null),
            self::toNullableString($data['VIN'] ?? null),
            self::toNullableString($data['Make'] ?? null),
            self::toNullableInt($data['Year'] ?? null),
            self::toNullableString($data['Model'] ?? null),
            self::toNullableFloat($data['TankCapacity'] ?? null),
            self::toNullableFloat($data['HighwayMPG'] ?? null),
            self::toNullableFloat($data['CityMPG'] ?? null),
            self::toNullableInt($data['FuelType'] ?? null),
            self::toNullableInt($data['VehicleSize'] ?? null),
            self::toNullableBool($data['HasNavigationDevice'] ?? null),
            self::toNullableBool($data['HasTachograph'] ?? null),
            self::toNullableInt($data['VehicleId'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function toNullableInt(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    private static function toNullableFloat(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }

    private static function toNullableBool(mixed $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }
}
