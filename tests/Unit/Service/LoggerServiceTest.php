<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Service;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;
use Superset\Config\LoggerConfig;
use Superset\Service\LoggerService;
use Superset\Tests\BaseTestCase;

#[CoversClass(LoggerService::class)]
#[Group('unit')]
#[Group('service')]
final class LoggerServiceTest extends BaseTestCase
{
    private string $logPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->logPath = sys_get_temp_dir() . '/test-log-' . uniqid() . '.log';
    }

    public function tearDown(): void
    {
        if (\file_exists($this->logPath)) {
            \unlink($this->logPath);
        }

        parent::tearDown();
    }

    public function testIsFinalAndReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(LoggerService::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testConstructorCreatesLoggerWithDefaultConfig(): void
    {
        $config = new LoggerConfig();
        $service = new LoggerService($config);

        $this->assertInstanceOf(LoggerInterface::class, $service->logger);
        $this->assertInstanceOf(Logger::class, $service->logger);
    }

    public function testConstructorCreatesLoggerWithCustomChannel(): void
    {
        $config = new LoggerConfig(channel: 'custom-channel');
        $service = new LoggerService($config);

        $logger = $service->logger;
        $this->assertInstanceOf(Logger::class, $logger);

        \assert($logger instanceof Logger);
        $this->assertSame('custom-channel', $logger->getName());
    }

    public function testConstructorCreatesLoggerWithCustomLogPath(): void
    {
        $config = new LoggerConfig(logPath: $this->logPath);
        $service = new LoggerService($config);

        $logger = $service->logger;
        \assert($logger instanceof Logger);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);
        $this->assertInstanceOf(StreamHandler::class, $handlers[0]);

        $stream = $this->getPrivateProperty($handlers[0], 'stream');
        $url = $this->getPrivateProperty($handlers[0], 'url');

        $this->assertTrue(\is_resource($stream) || $url === $this->logPath);
    }

    public function testConstructorCreatesLoggerWithCustomLevel(): void
    {
        $config = new LoggerConfig(level: Level::Debug);
        $service = new LoggerService($config);

        $logger = $service->logger;
        \assert($logger instanceof Logger);

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);

        \assert($handler instanceof StreamHandler);
        $this->assertSame(Level::Debug, $handler->getLevel());
    }

    public function testConstructorCreatesLoggerWithAllCustomParameters(): void
    {
        $config = new LoggerConfig(
            channel: 'full-custom',
            logPath: $this->logPath,
            level: Level::Warning,
        );
        $service = new LoggerService($config);

        $logger = $service->logger;
        \assert($logger instanceof Logger);

        $this->assertSame('full-custom', $logger->getName());

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);

        \assert($handler instanceof StreamHandler);
        $this->assertSame(Level::Warning, $handler->getLevel());
    }

    public function testGetReturnsLoggerInstance(): void
    {
        $config = new LoggerConfig();
        $service = new LoggerService($config);

        $logger = $service->get();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertSame($service->logger, $logger);
    }

    public function testGetReturnsSameInstanceAsPublicProperty(): void
    {
        $config = new LoggerConfig();
        $service = new LoggerService($config);

        $this->assertSame($service->logger, $service->get());
    }

    public function testLoggerCanWriteMessages(): void
    {
        $config = new LoggerConfig(logPath: $this->logPath, level: Level::Debug);
        $service = new LoggerService($config);

        $logger = $service->get();
        $logger->info('Test message');
        $logger->debug('Debug message');

        $this->assertFileExists($this->logPath);

        $content = \file_get_contents($this->logPath);
        $this->assertStringContainsString('Test message', $content);
        $this->assertStringContainsString('Debug message', $content);
    }

    public function testLoggerRespectsLogLevel(): void
    {
        $config = new LoggerConfig(logPath: $this->logPath, level: Level::Warning);
        $service = new LoggerService($config);

        $logger = $service->get();
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->error('Error message');

        $content = \file_get_contents($this->logPath);
        $this->assertStringNotContainsString('Debug message', $content);
        $this->assertStringNotContainsString('Info message', $content);
        $this->assertStringContainsString('Warning message', $content);
        $this->assertStringContainsString('Error message', $content);
    }

    public function testLoggerPropertyIsPubliclyAccessible(): void
    {
        $reflection = new \ReflectionProperty(LoggerService::class, 'logger');
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isReadOnly());
    }
}
