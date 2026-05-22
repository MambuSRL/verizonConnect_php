<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class ContentResourceByVehicleNumberVehicleStatus
{
    public function __construct(
        public readonly ?string $vehicleNumber,
        public readonly ?int $statusCode,
        public readonly ?ContentResourceVehicleStatus $contentResource,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $contentResource = null;
        if (isset($data['ContentResource']) && is_array($data['ContentResource'])) {
            $contentResource = ContentResourceVehicleStatus::fromArray($data['ContentResource']);
        }

        return new self(
            is_string($data['VehicleNumber'] ?? null) ? $data['VehicleNumber'] : null,
            is_int($data['StatusCode'] ?? null) ? $data['StatusCode'] : null,
            $contentResource,
        );
    }
}
