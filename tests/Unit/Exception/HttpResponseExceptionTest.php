<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\AbstractException;
use Superset\Exception\HttpResponseException;
use Superset\Tests\BaseTestCase;

#[CoversClass(HttpResponseException::class)]
#[Group('unit')]
#[Group('exception')]
final class HttpResponseExceptionTest extends BaseTestCase
{
    public function testExtendsAbstractException(): void
    {
        $exception = new HttpResponseException();

        $this->assertInstanceOf(AbstractException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $exception = new HttpResponseException();

        $this->assertSame('Unexpected HTTP response from Superset.', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $message = 'Custom HTTP error message';
        $exception = new HttpResponseException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomCode(): void
    {
        $message = 'Bad request error';
        $code = 400;
        $exception = new HttpResponseException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPreviousException(): void
    {
        $message = 'Network timeout';
        $code = 408;
        $previous = new \RuntimeException('Connection timeout');
        $exception = new HttpResponseException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Server error occurred';
        $code = 503;
        $previous = new \Exception('Upstream service unavailable');
        $exception = new HttpResponseException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(HttpResponseException::class);

        $this->assertTrue($reflection->isFinal());
    }
}
