<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class Location
{
    public function __construct(
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?string $addressLine1,
        public readonly ?string $addressLine2,
        public readonly ?string $locality,
        public readonly ?string $administrativeArea,
        public readonly ?string $postalCode,
        public readonly ?string $country,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            self::toNullableFloat($data['Latitude'] ?? $data['lat'] ?? null),
            self::toNullableFloat($data['Longitude'] ?? $data['lng'] ?? null),
            self::toNullableString($data['AddressLine1'] ?? null),
            self::toNullableString($data['AddressLine2'] ?? null),
            self::toNullableString($data['Locality'] ?? null),
            self::toNullableString($data['AdministrativeArea'] ?? null),
            self::toNullableString($data['PostalCode'] ?? null),
            self::toNullableString($data['Country'] ?? null),
        );
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromVehicleLocationArray(array $data): self
    {
        $address = [];
        if (isset($data['Address']) && is_array($data['Address'])) {
            $address = $data['Address'];
        }

        return new self(
            self::toNullableFloat($data['Latitude'] ?? $data['lat'] ?? null),
            self::toNullableFloat($data['Longitude'] ?? $data['lng'] ?? null),
            self::toNullableString($address['AddressLine1'] ?? null),
            self::toNullableString($address['AddressLine2'] ?? null),
            self::toNullableString($address['Locality'] ?? null),
            self::toNullableString($address['AdministrativeArea'] ?? null),
            self::toNullableString($address['PostalCode'] ?? null),
            self::toNullableString($address['Country'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    private static function toNullableFloat(mixed $value): ?float
    {
        return is_int($value) || is_float($value) ? (float) $value : null;
    }
}
