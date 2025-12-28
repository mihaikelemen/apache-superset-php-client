<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\SerializerConfig;
use Superset\Tests\BaseTestCase;

#[CoversClass(SerializerConfig::class)]
#[Group('unit')]
#[Group('config')]
final class SerializerConfigTest extends BaseTestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $config = new SerializerConfig();

        $this->assertSame(\DateTime::ATOM, $config->dateTimeFormat);
        $this->assertSame('UTC', $config->timeZone);
    }

    public function testConstructorWithAllParameters(): void
    {
        $format = 'Y-m-d\TH:i:s.uP';
        $timeZone = 'America/New_York';
        $config = new SerializerConfig($format, $timeZone);

        $this->assertSame($format, $config->dateTimeFormat);
        $this->assertSame($timeZone, $config->timeZone);
    }

    public function testDefaultConstants(): void
    {
        $this->assertSame(\DateTime::ATOM, SerializerConfig::DEFAULT_DATE_TIME_FORMAT);
        $this->assertSame('UTC', SerializerConfig::DEFAULT_TIME_ZONE);
    }

    public function testWithCustomConfigCreatesNewInstance(): void
    {
        $originalConfig = new SerializerConfig('Y-m-d', 'Europe/Paris');
        $newConfig = $originalConfig->withCustomConfig();

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('Y-m-d', $newConfig->dateTimeFormat);
        $this->assertSame('Europe/Paris', $newConfig->timeZone);
    }

    public function testWithCustomConfigWithPartialParameters(): void
    {
        $originalConfig = new SerializerConfig('Y-m-d H:i:s', 'UTC');

        $withFormat = $originalConfig->withCustomConfig('d/m/Y');
        $this->assertSame('d/m/Y', $withFormat->dateTimeFormat);
        $this->assertSame('UTC', $withFormat->timeZone);

        $withTimeZone = $originalConfig->withCustomConfig(null, 'Asia/Tokyo');
        $this->assertSame('Y-m-d H:i:s', $withTimeZone->dateTimeFormat);
        $this->assertSame('Asia/Tokyo', $withTimeZone->timeZone);
    }

    public function testWithCustomConfigWithBothParameters(): void
    {
        $originalConfig = new SerializerConfig('Y-m-d', 'UTC');
        $newConfig = $originalConfig->withCustomConfig('d/m/Y H:i:s', 'Australia/Sydney');

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('d/m/Y H:i:s', $newConfig->dateTimeFormat);
        $this->assertSame('Australia/Sydney', $newConfig->timeZone);
        $this->assertSame('Y-m-d', $originalConfig->dateTimeFormat);
        $this->assertSame('UTC', $originalConfig->timeZone);
    }

    public function testReadonlyProperties(): void
    {
        $config = new SerializerConfig();
        $reflection = new \ReflectionClass($config);

        $dateTimeFormatProperty = $reflection->getProperty('dateTimeFormat');
        $timeZoneProperty = $reflection->getProperty('timeZone');

        $this->assertTrue($dateTimeFormatProperty->isReadOnly());
        $this->assertTrue($timeZoneProperty->isReadOnly());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SerializerConfig::class);

        $this->assertTrue($reflection->isFinal());
    }
}
