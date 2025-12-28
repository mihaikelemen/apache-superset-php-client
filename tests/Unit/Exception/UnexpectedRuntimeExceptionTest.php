<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\AbstractException;
use Superset\Exception\UnexpectedRuntimeException;
use Superset\Tests\BaseTestCase;

#[CoversClass(UnexpectedRuntimeException::class)]
#[Group('unit')]
#[Group('exception')]
final class UnexpectedRuntimeExceptionTest extends BaseTestCase
{
    public function testExtendsAbstractException(): void
    {
        $exception = new UnexpectedRuntimeException();

        $this->assertInstanceOf(AbstractException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $exception = new UnexpectedRuntimeException();

        $this->assertSame('An unexpected runtime error occurred in Superset integration.', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $message = 'Configuration validation failed';
        $exception = new UnexpectedRuntimeException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomCode(): void
    {
        $message = 'Unexpected state encountered';
        $code = 503;
        $exception = new UnexpectedRuntimeException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPreviousException(): void
    {
        $message = 'Runtime operation failed';
        $code = 500;
        $previous = new \RuntimeException('Dependency not available');
        $exception = new UnexpectedRuntimeException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Critical runtime failure';
        $code = 502;
        $previous = new \Exception('Service dependency failed');
        $exception = new UnexpectedRuntimeException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(UnexpectedRuntimeException::class);

        $this->assertTrue($reflection->isFinal());
    }
}
