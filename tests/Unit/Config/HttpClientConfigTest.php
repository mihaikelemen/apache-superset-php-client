<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\HttpClientConfig;
use Superset\Exception\UnexpectedRuntimeException;
use Superset\Tests\BaseTestCase;

#[CoversClass(HttpClientConfig::class)]
#[Group('unit')]
#[Group('config')]
final class HttpClientConfigTest extends BaseTestCase
{
    public function testConstructorWithRequiredParameters(): void
    {
        $baseUrl = self::BASE_URL;
        $config = new HttpClientConfig($baseUrl);

        $this->assertSame($baseUrl, $config->baseUrl);
        $this->assertSame(15, $config->timeout);
        $this->assertSame(3, $config->maxRedirects);
        $this->assertSame('Superset-PHP-Client/1.0', $config->userAgent);
        $this->assertFalse($config->verifySsl);
        $this->assertTrue($config->followRedirects);
    }

    public function testConstructorWithCustomTimeout(): void
    {
        $baseUrl = self::BASE_URL;
        $timeout = 30;
        $config = new HttpClientConfig($baseUrl, $timeout);

        $this->assertSame($baseUrl, $config->baseUrl);
        $this->assertSame($timeout, $config->timeout);
        $this->assertSame(3, $config->maxRedirects);
        $this->assertSame('Superset-PHP-Client/1.0', $config->userAgent);
        $this->assertFalse($config->verifySsl);
        $this->assertTrue($config->followRedirects);
    }

    public function testConstructorWithAllParameters(): void
    {
        $baseUrl = self::BASE_URL;
        $timeout = 60;
        $maxRedirects = 5;
        $userAgent = 'CustomClient/2.0';
        $verifySsl = true;
        $followRedirects = false;

        $config = new HttpClientConfig(
            $baseUrl,
            $timeout,
            $maxRedirects,
            $userAgent,
            $verifySsl,
            $followRedirects
        );

        $this->assertSame($baseUrl, $config->baseUrl);
        $this->assertSame($timeout, $config->timeout);
        $this->assertSame($maxRedirects, $config->maxRedirects);
        $this->assertSame($userAgent, $config->userAgent);
        $this->assertSame($verifySsl, $config->verifySsl);
        $this->assertSame($followRedirects, $config->followRedirects);
    }

    public function testWithCustomConfigWithAllParameters(): void
    {
        $config = new HttpClientConfig(self::BASE_URL);
        $newConfig = $config->withCustomConfig(
            'https://superset.new.example.com',
            30,
            5,
            'CustomAgent/2.0',
            true,
            false
        );

        $this->assertSame('https://superset.new.example.com', $newConfig->baseUrl);
        $this->assertSame(30, $newConfig->timeout);
        $this->assertSame(5, $newConfig->maxRedirects);
        $this->assertSame('CustomAgent/2.0', $newConfig->userAgent);
        $this->assertTrue($newConfig->verifySsl);
        $this->assertFalse($newConfig->followRedirects);
    }

    public function testWithCustomConfigWithPartialParameters(): void
    {
        $config = new HttpClientConfig(self::BASE_URL, 20, 4, 'Agent/1.0', true, false);
        $newConfig = $config->withCustomConfig(timeout: 50, verifySsl: false);

        $this->assertSame(self::BASE_URL, $newConfig->baseUrl);
        $this->assertSame(50, $newConfig->timeout);
        $this->assertSame(4, $newConfig->maxRedirects);
        $this->assertSame('Agent/1.0', $newConfig->userAgent);
        $this->assertFalse($newConfig->verifySsl);
        $this->assertFalse($newConfig->followRedirects);
    }

    public function testWithCustomConfigWithNoParameters(): void
    {
        $config = new HttpClientConfig(self::BASE_URL, 25, 2, 'Test/1.0', true, true);
        $newConfig = $config->withCustomConfig();

        $this->assertSame(self::BASE_URL, $newConfig->baseUrl);
        $this->assertSame(25, $newConfig->timeout);
        $this->assertSame(2, $newConfig->maxRedirects);
        $this->assertSame('Test/1.0', $newConfig->userAgent);
        $this->assertTrue($newConfig->verifySsl);
        $this->assertTrue($newConfig->followRedirects);
    }

    public function testWithCustomConfigCreatesNewInstance(): void
    {
        $config = new HttpClientConfig(self::BASE_URL);
        $newConfig = $config->withCustomConfig(baseUrl: 'https://superset.new.example.com');

        $this->assertNotSame($config, $newConfig);
        $this->assertSame(self::BASE_URL, $config->baseUrl);
        $this->assertSame('https://superset.new.example.com', $newConfig->baseUrl);
    }

    public function testReadonlyProperties(): void
    {
        $config = new HttpClientConfig(self::BASE_URL);
        $reflection = new \ReflectionClass($config);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $this->assertTrue($property->isReadOnly());
        }
    }

    public function testPropertyTypes(): void
    {
        $config = new HttpClientConfig(
            'https://typed.example.com',
            45,
            7,
            'TypedClient/3.0',
            true,
            false
        );

        $this->assertIsString($config->baseUrl);
        $this->assertIsInt($config->timeout);
        $this->assertIsInt($config->maxRedirects);
        $this->assertIsString($config->userAgent);
        $this->assertIsBool($config->verifySsl);
        $this->assertIsBool($config->followRedirects);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(HttpClientConfig::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testConstructorWithValidDebugResource(): void
    {
        $debugResource = \fopen('php://memory', 'w');
        $config = new HttpClientConfig(
            self::BASE_URL,
            debug: $debugResource
        );

        $this->assertIsResource($config->debug);
        $this->assertSame($debugResource, $config->debug);

        \fclose($debugResource);
    }

    public function testConstructorWithNullDebug(): void
    {
        $config = new HttpClientConfig(self::BASE_URL, debug: null);

        $this->assertNull($config->debug);
    }

    public function testConstructorThrowsExceptionForBooleanDebug(): void
    {
        $this->expectException(UnexpectedRuntimeException::class);
        $this->expectExceptionMessage('The debug parameter must be a valid resource. Got boolean instead.');

        new HttpClientConfig(self::BASE_URL, debug: true);
    }
}
