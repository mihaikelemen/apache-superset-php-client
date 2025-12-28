<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\ApiConfig;
use Superset\Http\UrlBuilder;
use Superset\Tests\BaseTestCase;

#[CoversClass(UrlBuilder::class)]
#[Group('unit')]
#[Group('http')]
final class UrlBuilderTest extends BaseTestCase
{
    private UrlBuilder $urlBuilder;
    private ApiConfig $apiConfig;

    protected function setUp(): void
    {
        $this->apiConfig = new ApiConfig();
        $this->urlBuilder = new UrlBuilder(self::BASE_URL, $this->apiConfig);
    }

    public function testConstructorSetsProperties(): void
    {
        $this->assertSame(self::BASE_URL, $this->urlBuilder->baseUrl);

        $config = $this->getPrivateProperty($this->urlBuilder, 'apiConfig');
        $this->assertSame($this->apiConfig, $config);
    }

    public function testBuildWithSimpleEndpoint(): void
    {
        $result = $this->urlBuilder->build('dashboard/123');

        $expected = $this->buildUrl('api/v1/dashboard/123');
        $this->assertSame($expected, $result);
    }

    public function testBuildWithLeadingSlashEndpoint(): void
    {
        $result = $this->urlBuilder->build('/dashboard/456');

        $expected = $this->buildUrl('api/v1/dashboard/456');
        $this->assertSame($expected, $result);
    }

    public function testBuildWithTrailingSlashBaseUrl(): void
    {
        $urlBuilder = new UrlBuilder(self::BASE_URL, $this->apiConfig);

        $result = $urlBuilder->build('users');

        $expected = $this->buildUrl('api/v1/users');
        $this->assertSame($expected, $result);
    }

    public function testBuildWithBothTrailingAndLeadingSlashes(): void
    {
        $urlBuilder = new UrlBuilder(self::BASE_URL, $this->apiConfig);

        $result = $urlBuilder->build('/users/');

        $expected = $this->buildUrl('api/v1/users/');
        $this->assertSame($expected, $result);
    }

    public function testBuildWithEmptyEndpoint(): void
    {
        $result = $this->urlBuilder->build('');

        $expected = $this->buildUrl('api/v1/');
        $this->assertSame($expected, $result);
    }

    public function testBuildWithCustomApiVersion(): void
    {
        $customConfig = new ApiConfig('v2');
        $urlBuilder = new UrlBuilder(self::BASE_URL, $customConfig);

        $result = $urlBuilder->build('endpoints');

        $expected = $this->buildUrl('api/v2/endpoints');
        $this->assertSame($expected, $result);
    }

    public function testReadonlyProperties(): void
    {
        $reflection = new \ReflectionClass($this->urlBuilder);

        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $configProperty = $reflection->getProperty('apiConfig');

        $this->assertTrue($baseUrlProperty->isReadOnly());
        $this->assertTrue($configProperty->isReadOnly());
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(UrlBuilder::class);

        $this->assertTrue($reflection->isFinal());
    }
}
