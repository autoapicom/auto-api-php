<?php

declare(strict_types=1);

namespace AutoApi\Tests\Exception;

use AutoApi\Exception\ApiException;
use AutoApi\Exception\AuthException;
use PHPUnit\Framework\TestCase;

class AuthExceptionTest extends TestCase
{
    public function testExtendsApiException(): void
    {
        $exception = new AuthException();

        $this->assertInstanceOf(ApiException::class, $exception);
    }

    public function testDefaultMessage(): void
    {
        $exception = new AuthException();

        $this->assertSame('Invalid or missing API key', $exception->getMessage());
    }

    public function testDefaultStatusCode(): void
    {
        $exception = new AuthException();

        $this->assertSame(401, $exception->getStatusCode());
    }

    public function testCustomMessage(): void
    {
        $exception = new AuthException('Access denied');

        $this->assertSame('Access denied', $exception->getMessage());
    }

    public function testCustomStatusCode(): void
    {
        $exception = new AuthException('Forbidden', 403);

        $this->assertSame(403, $exception->getStatusCode());
    }

    public function testResponseBodyIsNull(): void
    {
        $exception = new AuthException();

        $this->assertNull($exception->getResponseBody());
    }
}
