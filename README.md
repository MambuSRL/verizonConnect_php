# verizonConnect_php

PHP 8.3 library to call Verizon Reveal APIs:

- Get token (`/token`)
- List vehicles (`/cmd/v1/vehicles`)
- Get vehicle location (`/rad/v1/vehicles/{vehicle_number}/location`)
- Get vehicle status (`/rad/v1/vehicles/{vehicle_number}/status`)
- Get vehicle GPS history (`/rad/v1/vehicles/{vehicle_number}/status/history`)
- Get active DTCs (`/rad/v1/vehicles/getvehiclesactivedtcs`)
- Get vehicles locations in bulk (`POST /rad/v1/vehicles/locations`)
- Get vehicles statuses in bulk (`POST /rad/v1/vehicles/statuses`)
- Get vehicle ECM status (`/rad/v1/vehicles/{vehicle_number}/getecmstatusbyvehiclenumber`)
- Get vehicle DTC history (`/rad/v1/vehicles/{vehicle_number}/getdtchistorybyvehiclenumber`)

## Install

```bash
composer install
```

## Usage

```php
<?php

use MambuSRL\VerizonConnect\RevealClient;
use MambuSRL\VerizonConnect\RevealConfig;

$config = new RevealConfig(
    appId: 'YOUR_REVEAL_APP_ID',
    username: 'YOUR_INTEGRATION_USERNAME',
    password: 'YOUR_INTEGRATION_PASSWORD'
);

$client = new RevealClient($config);

$token = $client->getToken();

// CMD API
$vehicles = $client->listVehicles($token);

// RAD API - single vehicle
$location = $client->getVehicleLocation($token, 'vehicle_number');
$status = $client->getVehicleStatus($token, 'vehicle_number');
$ecmStatus = $client->getVehicleEcmStatus($token, 'vehicle_number');
$dtcHistory = $client->getVehicleDtcHistory($token, 'vehicle_number');

// RAD API - history
$history = $client->getVehicleHistory(
    $token,
    'vehicle_number',
    '2026-05-22T00:00:00Z',
    '2026-05-22T23:59:59Z'
);

// RAD API - active dtcs
$activeDtcs = $client->getVehiclesActiveDTCS($token);

// RAD API - bulk endpoints
$locationsByVehicle = $client->getVehiclesLocations($token, ['VEHICLE_1', 'VEHICLE_2']);
$statusesByVehicle = $client->getVehiclesStatuses($token, ['VEHICLE_1', 'VEHICLE_2']);
```

## Return Types

- `getToken`: `string`
- `listVehicles`: `array<mixed>`
- `getVehicleLocation`: `VehicleLocation`
- `getVehicleStatus`: `VehicleStatus`
- `getVehicleEcmStatus`: `VehicleECMStatus`
- `getVehicleDtcHistory`: `VehicleDTCHistory`
- `getVehicleHistory`: `array<GpsHistoryByVehicleNumberResponse>`
- `getVehiclesActiveDTCS`: `array<VehiclesActiveDTC>`
- `getVehiclesLocations`: `array<ContentResourceByVehicleNumberVehicleLocation>`
- `getVehiclesStatuses`: `array<ContentResourceByVehicleNumberVehicleStatus>`

## Test

```bash
composer test
```
