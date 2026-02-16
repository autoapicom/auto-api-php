<?php

declare(strict_types=1);

namespace AutoApi\Tests\Exception;

use AutoApi\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ApiExceptionTest extends TestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new ApiException('Error', 500);

        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testGetMessage(): void
    {
        $exception = new ApiException('Something went wrong', 500);

        $this->assertSame('Something went wrong', $exception->getMessage());
    }

    public function testGetStatusCode(): void
    {
        $exception = new ApiException('Error', 422);

        $this->assertSame(422, $exception->getStatusCode());
    }

    public function testGetResponseBodyWhenProvided(): void
    {
        $body = ['message' => 'Validation failed', 'errors' => ['field' => 'required']];
        $exception = new ApiException('Error', 422, $body);

        $this->assertSame($body, $exception->getResponseBody());
    }

    public function testGetResponseBodyIsNullByDefault(): void
    {
        $exception = new ApiException('Error', 500);

        $this->assertNull($exception->getResponseBody());
    }

    public function testStatusCodeAvailableViaGetCode(): void
    {
        $exception = new ApiException('Error', 503);

        $this->assertSame(503, $exception->getCode());
    }
}
