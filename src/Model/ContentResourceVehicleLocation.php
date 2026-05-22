<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class ContentResourceVehicleLocation
{
    public function __construct(
        public readonly ?VehicleLocation $value,
        public readonly ?int $statusCode,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $value = null;
        if (isset($data['Value']) && is_array($data['Value'])) {
            $value = VehicleLocation::fromArray($data['Value']);
        }

        return new self(
            $value,
            is_int($data['StatusCode'] ?? null) ? $data['StatusCode'] : null,
        );
    }
}
