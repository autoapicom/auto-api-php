# Copilot Instructions — auto-api-php

This is a PHP 8.1+ client library for the auto-api.com vehicle data API.

## Architecture

- Single `Client` class (`src/Client.php`) with 6 public methods
- Every method takes `source` as its first parameter
- Guzzle 7 is used for all HTTP requests
- Methods return associative arrays (no DTOs or value objects)
- Authentication: `api_key` in query string for GET, `x-api-key` header for POST

## Exceptions

- `ApiException` — base exception for all API errors
- `AuthException` — thrown on 401/403 responses (extends ApiException)

## Conventions

- `declare(strict_types=1)` in every file
- PSR-4 autoloading: `AutoApi\` namespace maps to `src/`
- All code comments and documentation must be in English
- Keep it simple — no unnecessary abstractions or wrapper types
