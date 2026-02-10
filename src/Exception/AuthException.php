<?php

declare(strict_types=1);

namespace AutoApi\Exception;

class AuthException extends ApiException
{
    public function __construct(string $message = 'Invalid or missing API key', int $statusCode = 401)
    {
        parent::__construct($message, $statusCode);
    }
}
