<?php

/*
 * Copyright (c) 2026 MambuSRL
 * Author: MambuSRL
 */

declare(strict_types=1);

namespace MambuSRL\VerizonConnect\Model;

final class Segment
{
    public function __construct(
        public readonly ?string $startDateUtc,
        public readonly ?Location $startLocation,
        public readonly ?bool $startLocationIsPrivate,
        public readonly ?Location $endLocation,
        public readonly ?string $endDateUtc,
        public readonly ?bool $endLocationIsPrivate,
        public readonly ?bool $isComplete,
        public readonly ?float $distanceKilometers,
    ) {
    }

    /**
     * @param array<mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $startLocation = null;
        if (isset($data['StartLocation']) && is_array($data['StartLocation'])) {
            $startLocation = Location::fromArray($data['StartLocation']);
        }

        $endLocation = null;
        if (isset($data['EndLocation']) && is_array($data['EndLocation'])) {
            $endLocation = Location::fromArray($data['EndLocation']);
        }

        return new self(
            is_string($data['StartDateUtc'] ?? null) ? $data['StartDateUtc'] : null,
            $startLocation,
            is_bool($data['StartLocationIsPrivate'] ?? null) ? $data['StartLocationIsPrivate'] : null,
            $endLocation,
            is_string($data['EndDateUtc'] ?? null) ? $data['EndDateUtc'] : null,
            is_bool($data['EndLocationIsPrivate'] ?? null) ? $data['EndLocationIsPrivate'] : null,
            is_bool($data['IsComplete'] ?? null) ? $data['IsComplete'] : null,
            is_int($data['DistanceKilometers'] ?? null) || is_float($data['DistanceKilometers'] ?? null)
                ? (float) $data['DistanceKilometers']
                : null,
        );
    }
}
