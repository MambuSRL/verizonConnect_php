<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class VehicleDTCHistory
{
    /**
     * @param array<int, VehicleDTCHistoryDetails> $dtcs
     */
    public function __construct(
        public readonly ?string $vehicleNumber,
        public readonly ?string $vehicleName,
        public readonly array $dtcs,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $dtcs = [];
        if (isset($data['DTCs']) && is_array($data['DTCs'])) {
            foreach ($data['DTCs'] as $item) {
                if (is_array($item)) {
                    $dtcs[] = VehicleDTCHistoryDetails::fromArray($item);
                }
            }
        }

        return new self(
            is_string($data['VehicleNumber'] ?? null) ? $data['VehicleNumber'] : null,
            is_string($data['VehicleName'] ?? null) ? $data['VehicleName'] : null,
            $dtcs,
        );
    }
}
