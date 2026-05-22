<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class GpsHistoryByVehicleNumberResponse
{
    public function __construct(
        public readonly ?string $vehicleNumber,
        public readonly ?string $vehicleName,
        public readonly ?string $updateUtc,
        public readonly ?float $odometerInKM,
        public readonly ?bool $isPrivate,
        public readonly ?string $driverNumber,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?InternationalAddress $address,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?float $speed,
        public readonly ?float $batteryLevel,
        public readonly ?string $tractionBatteryChargingLastStartUtc,
        public readonly ?string $tractionBatteryChargingUtc,
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
            self::toNullableString($data['VehicleNumber'] ?? null),
            self::toNullableString($data['VehicleName'] ?? null),
            self::toNullableString($data['UpdateUtc'] ?? null),
            self::toNullableFloat($data['OdometerInKM'] ?? null),
            self::toNullableBool($data['IsPrivate'] ?? null),
            self::toNullableString($data['DriverNumber'] ?? null),
            self::toNullableString($data['FirstName'] ?? null),
            self::toNullableString($data['LastName'] ?? null),
            $address,
            self::toNullableFloat($data['Latitude'] ?? null),
            self::toNullableFloat($data['Longitude'] ?? null),
            self::toNullableFloat($data['Speed'] ?? null),
            self::toNullableFloat($data['BatteryLevel'] ?? null),
            self::toNullableString($data['TractionBatteryChargingLastStartUtc'] ?? null),
            self::toNullableString($data['TractionBatteryChargingUtc'] ?? null),
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

    private static function toNullableFloat(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }
}
