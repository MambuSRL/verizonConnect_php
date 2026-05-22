<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class InternationalAddress
{
    public function __construct(
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
            self::toNullableString($data['AddressLine1'] ?? null),
            self::toNullableString($data['AddressLine2'] ?? null),
            self::toNullableString($data['Locality'] ?? null),
            self::toNullableString($data['AdministrativeArea'] ?? null),
            self::toNullableString($data['PostalCode'] ?? null),
            self::toNullableString($data['Country'] ?? null),
        );
    }

    private static function toNullableString(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }
}
