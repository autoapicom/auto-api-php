# auto-api-client-php

[![Packagist Version](https://img.shields.io/packagist/v/auto-api/client)](https://packagist.org/packages/auto-api/client)
[![PHP Version](https://img.shields.io/packagist/dependency-v/auto-api/client/php)](https://packagist.org/packages/auto-api/client)
[![License](https://img.shields.io/packagist/l/auto-api/client)](LICENSE)

PHP wrapper for [auto-api.com](https://auto-api.com) — unified access to car listings from encar, mobile.de, autoscout24, che168 and other marketplaces.

Covers 8 sources across Korea, Europe, China and UAE. Fetch offers, filter by brand/year/price, track listing changes over time. Requires PHP 8.1+ and Guzzle.

## Installation

```bash
composer require auto-api/client
```

## Usage

```php
use AutoApi\Client;

$client = new Client('your-api-key');
```

### Get filters

```php
$filters = $client->getFilters('encar');
```

### Search offers

```php
$offers = $client->getOffers('mobilede', [
    'page' => 1,
    'brand' => 'BMW',
    'year_from' => 2020,
]);

// Pagination
$page = $offers['meta']['page'];
$nextPage = $offers['meta']['next_page'];
```

### Get single offer

```php
$offer = $client->getOffer('encar', '40427050');
```

### Track changes

```php
$changeId = $client->getChangeId('encar', '2025-01-15');
$changes = $client->getChanges('encar', $changeId);

// Next batch
$nextChangeId = $changes['meta']['next_change_id'];
$moreChanges = $client->getChanges('encar', $nextChangeId);
```

### Get offer by URL

```php
$info = $client->getOfferByUrl('https://encar.com/dc/dc_cardetailview.do?carid=40427050');
```

### Error handling

```php
use AutoApi\Exception\AuthException;
use AutoApi\Exception\ApiException;

try {
    $offers = $client->getOffers('encar', ['page' => 1]);
} catch (AuthException $e) {
    // 401/403 — invalid API key
} catch (ApiException $e) {
    echo $e->getStatusCode();
    echo $e->getMessage();
}
```

## Supported sources

| Source | Platform | Region |
|--------|----------|--------|
| `encar` | [encar.com](https://encar.com) | South Korea |
| `mobilede` | [mobile.de](https://mobile.de) | Germany |
| `autoscout24` | [autoscout24.com](https://autoscout24.com) | Europe |
| `che168` | [che168.com](https://che168.com) | China |
| `dongchedi` | [dongchedi.com](https://dongchedi.com) | China |
| `guazi` | [guazi.com](https://guazi.com) | China |
| `dubicars` | [dubicars.com](https://dubicars.com) | UAE |
| `dubizzle` | [dubizzle.com](https://dubizzle.com) | UAE |

## Other languages

| Language | Package |
|----------|---------|
| TypeScript | [@auto-api/client](https://github.com/autoapicom/auto-api-node) |
| Python | [auto-api-client](https://github.com/autoapicom/auto-api-python) |
| Go | [auto-api-go](https://github.com/autoapicom/auto-api-go) |
| C# | [AutoApi.Client](https://github.com/autoapicom/auto-api-dotnet) |
| Java | [auto-api-client](https://github.com/autoapicom/auto-api-java) |
| Ruby | [auto-api-client](https://github.com/autoapicom/auto-api-ruby) |
| Rust | [auto-api-client](https://github.com/autoapicom/auto-api-rust) |

## Documentation

[auto-api.com](https://auto-api.com)
