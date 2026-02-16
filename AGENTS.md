# auto-api PHP Client

PHP client for [auto-api.com](https://auto-api.com) — car listings API across 8 marketplaces.

## Quick Start

```bash
composer require autoapi/client
```

```php
use AutoApi\Client;

$client = new Client('your-api-key', 'https://api1.auto-api.com');
$offers = $client->getOffers('encar', ['page' => 1, 'brand' => 'BMW']);
```

## Build & Test

```bash
composer install
composer test
```

## Key Files

- `src/Client.php` — Single client class, all 6 public API methods
- `src/Exception/ApiException.php` — Base exception with status code and response body
- `src/Exception/AuthException.php` — Auth errors (401/403), extends ApiException
- `composer.json` — Package config, PSR-4 autoload under `AutoApi\`

## Conventions

- PHP 8.1+, strict types everywhere
- Guzzle 7 for HTTP
- PSR-4 autoloading: `AutoApi\` → `src/`
- Methods return associative arrays (decoded JSON), no DTO classes
- Source name passed as first parameter to each method
- All comments and docblocks in English

## API Methods

| Method | Params | Returns |
|--------|--------|---------|
| `getFilters($source)` | source name | `array` — brands, models, body types, etc. |
| `getOffers($source, $params)` | source + filters array | `array` — `{result: [], meta: {page, next_page}}` |
| `getOffer($source, $innerId)` | source + inner_id | `array` — single offer data |
| `getChangeId($source, $date)` | source + yyyy-mm-dd | `int` — numeric change_id |
| `getChanges($source, $changeId)` | source + change_id | `array` — `{result: [], meta: {next_change_id}}` |
| `getOfferByUrl($url)` | marketplace URL | `array` — offer data |
