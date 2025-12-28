<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\AbstractException;
use Superset\Exception\AuthenticationException;
use Superset\Tests\BaseTestCase;

#[CoversClass(AuthenticationException::class)]
#[Group('unit')]
#[Group('exception')]
final class AuthenticationExceptionTest extends BaseTestCase
{
    public function testExtendsAbstractException(): void
    {
        $exception = new AuthenticationException();

        $this->assertInstanceOf(AbstractException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testConstructorWithDefaultValues(): void
    {
        $exception = new AuthenticationException();

        $this->assertSame('Authentication failed.', $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomMessage(): void
    {
        $message = 'Invalid credentials provided';
        $exception = new AuthenticationException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(401, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithCustomCode(): void
    {
        $message = 'Access denied';
        $code = 403;
        $exception = new AuthenticationException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPreviousException(): void
    {
        $message = 'Token expired';
        $code = 401;
        $previous = new \RuntimeException('Session timeout');
        $exception = new AuthenticationException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorWithAllParameters(): void
    {
        $message = 'Multi-factor authentication required';
        $code = 403;
        $previous = new \Exception('MFA token missing');
        $exception = new AuthenticationException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(AuthenticationException::class);

        $this->assertTrue($reflection->isFinal());
    }
}
