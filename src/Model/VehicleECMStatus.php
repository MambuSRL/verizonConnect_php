<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class VehicleECMStatus
{
    /**
     * @param array<int, string> $sensorValues
     */
    public function __construct(
        public readonly ?float $currentOdometer,
        public readonly ?int $deviceTimeZoneOffset,
        public readonly ?bool $deviceTimeZoneUseDST,
        public readonly ?string $displayState,
        public readonly ?string $driverName,
        public readonly ?string $driverNumber,
        public readonly ?string $dtcs,
        public readonly ?int $engineMintutes,
        public readonly ?float $fuelLevelPercentage,
        public readonly ?int $idleTime,
        public readonly ?float $speed,
        public readonly array $sensorValues,
        public readonly ?string $updateUTC,
        public readonly ?float $totalFuelUsed,
        public readonly ?float $totalIdleFuel,
        public readonly ?float $totalPTOFuel,
        public readonly ?int $totalPTOTime,
        public readonly ?string $vin,
        public readonly ?float $batteryLevel,
        public readonly ?string $tractionBatteryChargingLastStartUtc,
        public readonly ?string $tractionBatteryChargingUtc,
        public readonly ?float $batteryVoltage,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableFloat($data['CurrentOdometer'] ?? null),
            self::toNullableInt($data['DeviceTimeZoneOffset'] ?? null),
            self::toNullableBool($data['DeviceTimeZoneUseDST'] ?? null),
            self::toNullableString($data['DisplayState'] ?? null),
            self::toNullableString($data['DriverName'] ?? null),
            self::toNullableString($data['DriverNumber'] ?? null),
            self::toNullableString($data['DTCs'] ?? null),
            self::toNullableInt($data['EngineMintutes'] ?? null),
            self::toNullableFloat($data['FuelLevelPercentage'] ?? null),
            self::toNullableInt($data['IdleTime'] ?? null),
            self::toNullableFloat($data['Speed'] ?? null),
            self::toStringList($data['SensorValues'] ?? []),
            self::toNullableString($data['UpdateUTC'] ?? null),
            self::toNullableFloat($data['TotalFuelUsed'] ?? null),
            self::toNullableFloat($data['TotalIdleFuel'] ?? null),
            self::toNullableFloat($data['TotalPTOFuel'] ?? null),
            self::toNullableInt($data['TotalPTOTime'] ?? null),
            self::toNullableString($data['VIN'] ?? null),
            self::toNullableFloat($data['BatteryLevel'] ?? null),
            self::toNullableString($data['TractionBatteryChargingLastStartUtc'] ?? null),
            self::toNullableString($data['TractionBatteryChargingUtc'] ?? null),
            self::toNullableFloat($data['BatteryVoltage'] ?? null),
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

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private static function toStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            if (is_string($item)) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
