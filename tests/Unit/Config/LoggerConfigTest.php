<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Config;

use Monolog\Level;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\LoggerConfig;
use Superset\Tests\BaseTestCase;

#[CoversClass(LoggerConfig::class)]
#[Group('unit')]
#[Group('config')]
final class LoggerConfigTest extends BaseTestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $config = new LoggerConfig();

        $this->assertSame('superset', $config->channel);
        $this->assertSame('php://stderr', $config->logPath);
        $this->assertSame(Level::Info, $config->level);
    }

    public function testConstructorWithAllParameters(): void
    {
        $channel = 'production';
        $logPath = '/var/log/production.log';
        $level = Level::Info;
        $config = new LoggerConfig($channel, $logPath, $level);

        $this->assertSame($channel, $config->channel);
        $this->assertSame($logPath, $config->logPath);
        $this->assertSame($level, $config->level);
    }

    public function testDefaultConstants(): void
    {
        $this->assertSame('superset', LoggerConfig::DEFAULT_CHANNEL);
        $this->assertSame('php://stderr', LoggerConfig::DEFAULT_LOG_PATH);
        $this->assertSame(Level::Info, LoggerConfig::DEFAULT_LOG_LEVEL);
    }

    public function testWithCustomConfigCreatesNewInstance(): void
    {
        $originalConfig = new LoggerConfig('test', '/tmp/test.log', Level::Debug);
        $newConfig = $originalConfig->withCustomConfig();

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('test', $newConfig->channel);
        $this->assertSame('/tmp/test.log', $newConfig->logPath);
        $this->assertSame(Level::Debug, $newConfig->level);
    }

    public function testWithCustomConfigWithPartialParameters(): void
    {
        $originalConfig = new LoggerConfig('original', '/var/log/original.log', Level::Warning);

        $withChannel = $originalConfig->withCustomConfig('updated');
        $this->assertSame('updated', $withChannel->channel);
        $this->assertSame('/var/log/original.log', $withChannel->logPath);
        $this->assertSame(Level::Warning, $withChannel->level);

        $withLogPath = $originalConfig->withCustomConfig(null, '/tmp/new.log');
        $this->assertSame('original', $withLogPath->channel);
        $this->assertSame('/tmp/new.log', $withLogPath->logPath);
        $this->assertSame(Level::Warning, $withLogPath->level);

        $withLevel = $originalConfig->withCustomConfig(null, null, Level::Critical);
        $this->assertSame('original', $withLevel->channel);
        $this->assertSame('/var/log/original.log', $withLevel->logPath);
        $this->assertSame(Level::Critical, $withLevel->level);
    }

    public function testWithCustomConfigWithAllParameters(): void
    {
        $originalConfig = new LoggerConfig('old', '/old.log', Level::Info);
        $newConfig = $originalConfig->withCustomConfig('new', '/new.log', Level::Emergency);

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('new', $newConfig->channel);
        $this->assertSame('/new.log', $newConfig->logPath);
        $this->assertSame(Level::Emergency, $newConfig->level);
        $this->assertSame('old', $originalConfig->channel);
        $this->assertSame('/old.log', $originalConfig->logPath);
        $this->assertSame(Level::Info, $originalConfig->level);
    }

    public function testReadonlyProperties(): void
    {
        $config = new LoggerConfig();
        $reflection = new \ReflectionClass($config);

        $channelProperty = $reflection->getProperty('channel');
        $logPathProperty = $reflection->getProperty('logPath');
        $levelProperty = $reflection->getProperty('level');

        $this->assertTrue($channelProperty->isReadOnly());
        $this->assertTrue($logPathProperty->isReadOnly());
        $this->assertTrue($levelProperty->isReadOnly());
        $this->assertTrue($reflection->isFinal());
    }
}
