<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\AbstractException;
use Superset\Exception\JsonDecodeException;
use Superset\Tests\BaseTestCase;

#[CoversClass(JsonDecodeException::class)]
#[Group('unit')]
#[Group('exception')]
final class JsonDecodeExceptionTest extends BaseTestCase
{
    public function testExtendsAbstractException(): void
    {
        $exception = new JsonDecodeException();

        $this->assertInstanceOf(AbstractException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $exception = new JsonDecodeException();

        $this->assertSame('Failed to decode JSON response from Superset.', $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $message = 'Invalid JSON syntax detected';
        $exception = new JsonDecodeException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(500, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomCode(): void
    {
        $message = 'Malformed JSON response';
        $code = 422;
        $exception = new JsonDecodeException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPreviousException(): void
    {
        $message = 'JSON parsing failed';
        $code = 400;
        $previous = new \JsonException('Syntax error');
        $exception = new JsonDecodeException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Complex JSON decode error';
        $code = 502;
        $previous = new \JsonException('Unexpected character');
        $exception = new JsonDecodeException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(JsonDecodeException::class);

        $this->assertTrue($reflection->isFinal());
    }
}
