# Claude Instructions — auto-api-php

## Language

All code comments, documentation, and commit messages must be in **English**.

## Commands

- Install dependencies: `composer install`
- Run tests: `composer test`

## Key Files

- `src/Client.php` — main client class with 6 public API methods
- `src/Exception/ApiException.php` — base API exception
- `src/Exception/AuthException.php` — authentication errors (401/403)
- `composer.json` — package configuration and dependencies

## Code Style

- PHP 8.1+ required
- `declare(strict_types=1)` in every file
- Guzzle 7 for HTTP requests
- PSR-4 autoloading: `AutoApi\` namespace maps to `src/`
- Methods return associative arrays — no DTO or value object classes
- `api_key` passed in query string for GET requests, `x-api-key` header for POST
- Keep the codebase simple — avoid unnecessary abstractions
- English comments only
