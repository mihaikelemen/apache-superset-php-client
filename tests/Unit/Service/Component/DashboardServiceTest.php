<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Service\Component;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\ApiConfig;
use Superset\Config\SerializerConfig;
use Superset\Dto\Dashboard;
use Superset\Exception\UnexpectedRuntimeException;
use Superset\Http\Contracts\HttpClientInterface;
use Superset\Http\UrlBuilder;
use Superset\Serializer\SerializerService;
use Superset\Service\Component\DashboardService;
use Superset\Tests\BaseTestCase;

#[CoversClass(DashboardService::class)]
#[Group('unit')]
#[Group('service')]
final class DashboardServiceTest extends BaseTestCase
{
    private UrlBuilder $urlBuilder;
    private SerializerService $serializer;

    public const UUID = 'a1b2c3d4-5e6f-4a7b-8c9d-0e1f2a3b4c5d';

    protected function setUp(): void
    {
        $this->urlBuilder = new UrlBuilder(self::BASE_URL, new ApiConfig());
        $this->serializer = SerializerService::create(new SerializerConfig());
    }

    private function dashboard(HttpClientInterface $httpClient): DashboardService
    {
        return new DashboardService($httpClient, $this->urlBuilder, $this->serializer);
    }

    public function testIsFinalAndReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(DashboardService::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testGetReturnsHydratedDashboardWithStringIdentity(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $dashboardData = [
            'id' => 123,
            'dashboard_title' => 'Test Dashboard',
            'slug' => 'test-dashboard',
            'published' => true,
        ];

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/test-slug'))
            ->willReturn(['result' => $dashboardData]);

        $dashboard = $this->dashboard($httpClient)->get('test-slug');

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame(123, $dashboard->id);
        $this->assertSame('Test Dashboard', $dashboard->title);
        $this->assertSame('test-dashboard', $dashboard->slug);
    }

    public function testGetReturnsHydratedDashboardWithIntIdentity(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $dashboardData = [
            'id' => 123,
            'dashboard_title' => 'Another Dashboard',
            'slug' => 'another-dashboard',
            'published' => false,
        ];

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123'))
            ->willReturn(['result' => $dashboardData]);

        $dashboard = $this->dashboard($httpClient)->get(123);

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame(123, $dashboard->id);
        $this->assertSame('Another Dashboard', $dashboard->title);
        $this->assertSame('another-dashboard', $dashboard->slug);
    }

    public function testGetThrowsExceptionWhenResultMissing(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage('invalid-id')
        );

        $this->dashboard($httpClient)->get('invalid-id');
    }

    public function testGetThrowsExceptionWhenResultNotArray(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => 'invalid']);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage(123)
        );

        $this->dashboard($httpClient)->get(123);
    }

    public function testUuidReturnsUuidStringWithIntIdentity(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard($httpClient)->uuid(123);

        $this->assertSame(self::UUID, $uuid);
    }

    public function testUuidReturnsUuidStringWithStringIdentity(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/test-slug/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard($httpClient)->uuid('test-slug');

        $this->assertSame(self::UUID, $uuid);
    }

    public function testUuidReturnsValidV4Uuid(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard/123/embedded'))
            ->willReturn(['result' => ['uuid' => self::UUID]]);

        $uuid = $this->dashboard($httpClient)->uuid(123);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $uuid
        );
    }

    public function testUuidThrowsExceptionWhenResultMissing(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage(123, 'UUID')
        );

        $this->dashboard($httpClient)->uuid(123);
    }

    public function testUuidThrowsExceptionWhenUuidMissing(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => []]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage('slug', 'UUID')
        );

        $this->dashboard($httpClient)->uuid('slug');
    }

    public function testUuidThrowsExceptionWhenUuidNotString(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => ['uuid' => 456]]);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            $this->errorMessage(123, 'UUID')
        );

        $this->dashboard($httpClient)->uuid(123);
    }

    public function testListReturnsArrayOfDashboards(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $dashboardsData = [
            ['id' => 1, 'dashboard_title' => 'First', 'slug' => 'first', 'published' => true],
            ['id' => 2, 'dashboard_title' => 'Second', 'slug' => 'second', 'published' => true],
        ];

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), [])
            ->willReturn(['result' => $dashboardsData]);

        $dashboards = $this->dashboard($httpClient)->list();

        $this->assertIsArray($dashboards);
        $this->assertCount(2, $dashboards);
        $this->assertContainsOnlyInstancesOf(Dashboard::class, $dashboards);
        $this->assertSame('First', $dashboards[0]->title);
        $this->assertSame('Second', $dashboards[1]->title);
    }

    public function testListReturnsEmptyArrayWhenNoResult(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $dashboards = $this->dashboard($httpClient)->list();

        $this->assertSame([], $dashboards);
    }

    public function testListThrowsExceptionWhenResultNotArray(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => 'invalid']);

        $this->expectExceptionWithMessage(
            UnexpectedRuntimeException::class,
            'Invalid dashboards data format received from API'
        );

        $this->dashboard($httpClient)->list();
    }

    public function testListWithTagParameter(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

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

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), $expectedParams)
            ->willReturn(['result' => []]);

        $this->dashboard($httpClient)->list('production');
    }

    public function testListWithOnlyPublishedTrue(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), ['published' => 'true'])
            ->willReturn(['result' => []]);

        $this->dashboard($httpClient)->list(null, true);
    }

    public function testListWithOnlyPublishedFalse(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), ['published' => 'false'])
            ->willReturn(['result' => []]);

        $this->dashboard($httpClient)->list(null, false);
    }

    public function testListWithBothTagAndPublished(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

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

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with($this->buildUrl('api/v1/dashboard'), $expectedParams)
            ->willReturn(['result' => []]);

        $this->dashboard($httpClient)->list('test', true);
    }

    public function testListSkipsNonArrayItems(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $dashboardsData = [
            ['id' => 1, 'dashboard_title' => 'First', 'slug' => 'first'],
            'invalid-item',
            ['id' => 2, 'dashboard_title' => 'Second', 'slug' => 'second'],
            null,
            ['id' => 3, 'dashboard_title' => 'Third', 'slug' => 'third'],
        ];

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => $dashboardsData]);

        $dashboards = $this->dashboard($httpClient)->list();

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
