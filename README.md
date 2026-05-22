# verizonConnect_php

PHP 8.3 library to call Verizon Reveal APIs:

- Get token (`/token`)
- List vehicles (`/cmd/v1/vehicles`)
- Get vehicle location (`/rad/v1/vehicles/{vehicle_number}/location`)

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
$vehicles = $client->listVehicles($token);
$location = $client->getVehicleLocation($token, 'vehicle_number');
```

## Test

```bash
composer test
```
