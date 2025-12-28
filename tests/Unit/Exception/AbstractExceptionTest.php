<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Superset\Exception\AbstractException;
use Superset\Tests\BaseTestCase;

#[CoversClass(AbstractException::class)]
#[Group('unit')]
#[Group('exception')]
final class AbstractExceptionTest extends BaseTestCase
{
    public function testExtendsStandardException(): void
    {
        $exception = $this->createConcreteException();

        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testConstructorWithBasicParameters(): void
    {
        $message = 'Test message';
        $code = 500;
        $previous = new \RuntimeException('Previous exception');

        $exception = $this->createConcreteException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testConstructorLogsWithLogger(): void
    {
        $message = 'Test message';
        $code = 404;
        $previous = new \RuntimeException('Database error');
        $context = ['table' => 'users'];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with($message, $this->callback(fn ($log) => $log['code'] === $code
                && $log['context'] === $context
                && $log['previous'] === $previous->getMessage()
                && isset($log['exception'])));

        $this->createConcreteException($message, $code, $previous, $context, $logger);
    }

    public function testConstructorLogsWithLoggerAndNoPrevious(): void
    {
        $message = 'Test message';
        $code = 400;
        $context = ['field' => 'email'];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with($message, $this->callback(fn ($log) => $log['code'] === $code
                && $log['context'] === $context
                && null === $log['previous']
                && isset($log['exception'])));

        $this->createConcreteException($message, $code, null, $context, $logger);
    }

    public function testConstructorWithoutLoggerDoesNotLog(): void
    {
        $exception = $this->createConcreteException('Test', 500, null, ['key' => 'value']);

        $this->assertInstanceOf(AbstractException::class, $exception);
    }

    private function createConcreteException(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = [],
        ?LoggerInterface $logger = null,
    ): AbstractException {
        return new class($message, $code, $previous, $context, $logger) extends AbstractException {};
    }
}
