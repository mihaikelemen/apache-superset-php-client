<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\AbstractException;
use Superset\Exception\SerializationException;
use Superset\Tests\BaseTestCase;

#[CoversClass(SerializationException::class)]
#[Group('unit')]
#[Group('exception')]
final class SerializationExceptionTest extends BaseTestCase
{
    public function testExtendsAbstractException(): void
    {
        $exception = new SerializationException();

        $this->assertInstanceOf(AbstractException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $exception = new SerializationException();

        $this->assertSame('An error occurred during serialization/deserialization.', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $message = 'Failed to serialize dashboard object';
        $exception = new SerializationException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomCode(): void
    {
        $message = 'Deserialization type mismatch';
        $code = 422;
        $exception = new SerializationException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPreviousException(): void
    {
        $message = 'Normalizer context error';
        $code = 500;
        $previous = new \RuntimeException('Context builder failed');
        $exception = new SerializationException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Property accessor violation';
        $code = 400;
        $previous = new \Exception('Cannot access private property');
        $exception = new SerializationException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SerializationException::class);

        $this->assertTrue($reflection->isFinal());
    }
}
