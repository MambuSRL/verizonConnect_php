<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class DriverVehicleSegments
{
    /**
     * @param array<int, Segment> $segments
     */
    public function __construct(
        public readonly ?DriverInfo $driver,
        public readonly ?VehicleInfo $vehicle,
        public readonly array $segments,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $driver = null;
        if (isset($data['Driver']) && is_array($data['Driver'])) {
            $driver = DriverInfo::fromArray($data['Driver']);
        }

        $vehicle = null;
        if (isset($data['Vehicle']) && is_array($data['Vehicle'])) {
            $vehicle = VehicleInfo::fromArray($data['Vehicle']);
        }

        $segments = [];
        if (isset($data['Segments']) && is_array($data['Segments'])) {
            foreach ($data['Segments'] as $item) {
                if (is_array($item)) {
                    $segments[] = Segment::fromArray($item);
                }
            }
        }

        return new self($driver, $vehicle, $segments);
    }
}
