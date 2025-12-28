<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\ApiConfig;
use Superset\Tests\BaseTestCase;

#[CoversClass(ApiConfig::class)]
#[Group('unit')]
#[Group('config')]
final class ApiConfigTest extends BaseTestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $config = new ApiConfig();

        $this->assertSame('v1', $config->version);
        $this->assertSame([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $config->headers);
    }

    public function testConstructorWithCustomVersion(): void
    {
        $version = 'v2';
        $config = new ApiConfig($version);

        $this->assertSame($version, $config->version);
        $this->assertSame([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $config->headers);
    }

    public function testConstructorWithCustomHeaders(): void
    {
        $version = 'v1';
        $headers = ['X-Custom-Header' => 'custom-value'];
        $config = new ApiConfig($version, $headers);

        $this->assertSame($version, $config->version);
        $this->assertSame($headers, $config->headers);
    }

    public function testConstructorWithAllParameters(): void
    {
        $version = 'v3';
        $headers = [
            'Authorization' => 'Bearer token123',
            'Accept' => 'application/json',
            'User-Agent' => 'TestClient/1.0',
        ];
        $config = new ApiConfig($version, $headers);

        $this->assertSame($version, $config->version);
        $this->assertSame($headers, $config->headers);
    }

    public function testWithCustomConfigWithEmptyParameters(): void
    {
        $originalConfig = new ApiConfig('v1', ['Original' => 'value']);
        $newConfig = $originalConfig->withCustomConfig();

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('v1', $newConfig->version);
        $this->assertSame(['Original' => 'value'], $newConfig->headers);
    }

    public function testWithCustomConfigWithNewVersion(): void
    {
        $originalConfig = new ApiConfig('v1');
        $newConfig = $originalConfig->withCustomConfig('v2');

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('v2', $newConfig->version);
        $this->assertSame([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $newConfig->headers);
    }

    public function testWithCustomConfigWithNewHeaders(): void
    {
        $originalConfig = new ApiConfig('v2', ['Original' => 'value']);
        $customHeaders = ['New-Header' => 'new-value', 'Another' => 'another-value'];
        $newConfig = $originalConfig->withCustomConfig(null, $customHeaders);

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('v2', $newConfig->version);
        $this->assertSame([
            'New-Header' => 'new-value',
            'Another' => 'another-value',
        ], $newConfig->headers);
    }

    public function testWithCustomConfigFilteringNullValues(): void
    {
        $originalConfig = new ApiConfig('v1', ['Existing' => 'value']);
        $customHeaders = [
            'Valid' => 'valid-value',
            'NullValue' => null,
            'EmptyString' => '',
            'Zero' => 0,
            'False' => false,
        ];
        $newConfig = $originalConfig->withCustomConfig(null, $customHeaders);

        $expectedHeaders = [
            'Valid' => 'valid-value',
            'EmptyString' => '',
            'Zero' => 0,
            'False' => false,
        ];

        $this->assertSame($expectedHeaders, $newConfig->headers);
    }

    public function testWithCustomConfigWithBothParameters(): void
    {
        $originalConfig = new ApiConfig('v1', ['Original' => 'value']);
        $customHeaders = ['New' => 'new-value'];
        $newConfig = $originalConfig->withCustomConfig('v3', $customHeaders);

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('v3', $newConfig->version);
        $this->assertSame(['New' => 'new-value'], $newConfig->headers);
    }

    public function testWithCustomConfigWithEmptyHeadersArray(): void
    {
        $originalConfig = new ApiConfig('v1', ['Original' => 'value']);
        $newConfig = $originalConfig->withCustomConfig(null, []);

        $this->assertNotSame($originalConfig, $newConfig);
        $this->assertSame('v1', $newConfig->version);
        $this->assertSame([], $newConfig->headers);
    }

    public function testReadonlyProperties(): void
    {
        $config = new ApiConfig('test', ['header' => 'value']);
        $reflection = new \ReflectionClass($config);

        $versionProperty = $reflection->getProperty('version');
        $headersProperty = $reflection->getProperty('headers');

        $this->assertTrue($versionProperty->isReadOnly());
        $this->assertTrue($headersProperty->isReadOnly());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(ApiConfig::class);

        $this->assertTrue($reflection->isFinal());
    }
}
