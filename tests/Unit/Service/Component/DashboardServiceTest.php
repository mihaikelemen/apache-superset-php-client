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

    public const UUID = 'a1b2c3d4-5e6f-4a7b-8c9d-0e1f2a3b4c5d';

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

    public function testGetReturnsHydratedDashboardWithStringIdentity(): void
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

    public function testGetReturnsHydratedDashboardWithIntIdentity(): void
    {
        $dashboardData = [
            'id' => 123,
            'dashboard_title' => 'Another Dashboard',
            'slug' => 'another-dashboard',
            'published' => false,
        ];

        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123'))
            ->willReturn(['result' => $dashboardData]);

        $dashboard = $this->dashboard()->get(123);

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame(123, $dashboard->id);
        $this->assertSame('Another Dashboard', $dashboard->title);
        $this->assertSame('another-dashboard', $dashboard->slug);
    }

    public function testGetThrowsExceptionWhenResultMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage('invalid-id')
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
            $this->errorMessage(123)
        );

        $this->dashboard()->get(123);
    }

    public function testUuidReturnsUuidStringWithIntIdentity(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard()->uuid(123);

        $this->assertSame(self::UUID, $uuid);
    }

    public function testUuidReturnsUuidStringWithStringIdentity(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/test-slug/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard()->uuid('test-slug');

        $this->assertSame(self::UUID, $uuid);
    }

    public function testUuidReturnsValidV4Uuid(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard()->uuid(123);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function testUuidThrowsExceptionWhenResultMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage(123, 'UUID')
        );

        $this->dashboard()->uuid(123);
    }

    public function testUuidThrowsExceptionWhenUuidMissing(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => []]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage('slug', 'UUID')
        );

        $this->dashboard()->uuid('slug');
    }

    public function testUuidThrowsExceptionWhenUuidNotString(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => ['uuid' => 456]]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage(123, 'UUID')
        );

        $this->dashboard()->uuid(123);
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

    private function errorMessage(int|string $identity, string $type = 'data'): string
    {
        return \sprintf("Dashboard %s not found in response for dashboard identifier '%s'", $type, $identity);
    }
}
