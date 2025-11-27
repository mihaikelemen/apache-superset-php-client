<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Service\Component;

use Superset\Config\ApiConfig;
use Superset\Config\SerializerConfig;
use Superset\Dto\Dashboard;
use Superset\Exception\UnexpectedRuntimeException;
use Superset\Http\Contracts\HttpClientInterface;
use Superset\Http\UrlBuilder;
use Superset\Serializer\SerializerService;
use Superset\Service\Component\DashboardService;
use Superset\Tests\BaseTestCase;

/**
 * @group unit
 * @group service
 *
 * @covers \Superset\Service\Component\DashboardService
 */
final class DashboardServiceTest extends BaseTestCase
{
    private HttpClientInterface $httpClient;
    private UrlBuilder $urlBuilder;
    private SerializerService $serializer;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->urlBuilder = new UrlBuilder(self::BASE_URL, new ApiConfig());
        $this->serializer = SerializerService::create(new SerializerConfig());
    }

    private function dashboard(): DashboardService
    {
        return new DashboardService($this->httpClient, $this->urlBuilder, $this->serializer);
    }

    public function testIsFinalAndReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(DashboardService::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testGetReturnsHydratedDashboard(): void
    {
        $dashboardData = [
            'id' => 123,
            'dashboard_title' => 'Test Dashboard',
            'slug' => 'test-dashboard',
            'published' => true,
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/test-slug'))
            ->willReturn(['result' => $dashboardData]);

        $dashboard = $this->dashboard()->get('test-slug');

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame(123, $dashboard->id);
        $this->assertSame('Test Dashboard', $dashboard->title);
        $this->assertSame('test-dashboard', $dashboard->slug);
    }

    public function testGetThrowsExceptionWhenResultMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            "Dashboard data not found in response for dashboard identifier 'invalid-id'"
        );

        $this->dashboard()->get('invalid-id');
    }

    public function testGetThrowsExceptionWhenResultNotArray(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => 'invalid']);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            "Dashboard data not found in response for dashboard identifier '999'"
        );

        $this->dashboard()->get('999');
    }

    public function testUuidReturnsUuidString(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123/embedded'))
            ->willReturn(['result' => ['uuid' => 'abc-123-def-456']]);

        $uuid = $this->dashboard()->uuid('123');

        $this->assertSame('abc-123-def-456', $uuid);
    }

    public function testUuidThrowsExceptionWhenResultMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            "Dashboard UUID not found in response for dashboard identifier '456'"
        );

        $this->dashboard()->uuid('456');
    }

    public function testUuidThrowsExceptionWhenUuidMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => []]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            "Dashboard UUID not found in response for dashboard identifier '789'"
        );

        $this->dashboard()->uuid('789');
    }

    public function testUuidThrowsExceptionWhenUuidNotString(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => ['uuid' => 123]]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            "Dashboard UUID not found in response for dashboard identifier 'bad'"
        );

        $this->dashboard()->uuid('bad');
    }

    public function testListReturnsArrayOfDashboards(): void
    {
        $dashboardsData = [
            ['id' => 1, 'dashboard_title' => 'First', 'slug' => 'first', 'published' => true],
            ['id' => 2, 'dashboard_title' => 'Second', 'slug' => 'second', 'published' => true],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), [])
            ->willReturn(['result' => $dashboardsData]);

        $dashboards = $this->dashboard()->list();

        $this->assertIsArray($dashboards);
        $this->assertCount(2, $dashboards);
        $this->assertContainsOnlyInstancesOf(Dashboard::class, $dashboards);
        $this->assertSame('First', $dashboards[0]->title);
        $this->assertSame('Second', $dashboards[1]->title);
    }

    public function testListReturnsEmptyArrayWhenNoResult(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $dashboards = $this->dashboard()->list();

        $this->assertSame([], $dashboards);
    }

    public function testListThrowsExceptionWhenResultNotArray(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => 'invalid']);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            'Invalid dashboards data format received from API'
        );

        $this->dashboard()->list();
    }

    public function testListWithTagParameter(): void
    {
        $expectedParams = [
            'q' => \json_encode([
                'filters' => [
                    [
                        'col' => 'tags',
                        'opr' => 'dashboard_tags',
                        'value' => 'production',
                    ],
                ],
            ]),
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), $expectedParams)
            ->willReturn(['result' => []]);

        $this->dashboard()->list('production');
    }

    public function testListWithOnlyPublishedTrue(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), ['published' => 'true'])
            ->willReturn(['result' => []]);

        $this->dashboard()->list(null, true);
    }

    public function testListWithOnlyPublishedFalse(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), ['published' => 'false'])
            ->willReturn(['result' => []]);

        $this->dashboard()->list(null, false);
    }

    public function testListWithBothTagAndPublished(): void
    {
        $expectedParams = [
            'q' => \json_encode([
                'filters' => [
                    [
                        'col' => 'tags',
                        'opr' => 'dashboard_tags',
                        'value' => 'test',
                    ],
                ],
            ]),
            'published' => 'true',
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), $expectedParams)
            ->willReturn(['result' => []]);

        $this->dashboard()->list('test', true);
    }

    public function testListSkipsNonArrayItems(): void
    {
        $dashboardsData = [
            ['id' => 1, 'dashboard_title' => 'First', 'slug' => 'first'],
            'invalid-item',
            ['id' => 2, 'dashboard_title' => 'Second', 'slug' => 'second'],
            null,
            ['id' => 3, 'dashboard_title' => 'Third', 'slug' => 'third'],
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => $dashboardsData]);

        $dashboards = $this->dashboard()->list();

        $this->assertCount(3, $dashboards);
        $this->assertSame('First', $dashboards[0]->title);
        $this->assertSame('Second', $dashboards[1]->title);
        $this->assertSame('Third', $dashboards[2]->title);
    }
}
