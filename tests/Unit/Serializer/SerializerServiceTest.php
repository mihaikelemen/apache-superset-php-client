<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Serializer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Superset\Config\SerializerConfig;
use Superset\Dto\Dashboard;
use Superset\Exception\SerializationException;
use Superset\Serializer\SerializerService;
use Superset\Tests\BaseTestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(SerializerService::class)]
#[Group('unit')]
#[Group('serializer')]
final class SerializerServiceTest extends BaseTestCase
{
    private SerializerService $serializer;

    protected function setUp(): void
    {
        $this->serializer = SerializerService::create(new SerializerConfig());
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(SerializerService::class, $this->serializer);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SerializerService::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testIsReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(SerializerService::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(SerializerService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertSame('serializer', $parameters[0]->getName());
    }

    public function testConstructorParametersAreReadonly(): void
    {
        $reflection = new \ReflectionClass(SerializerService::class);
        $property = $reflection->getProperty('serializer');

        $this->assertTrue($property->isReadOnly());
    }

    public function testCreateMethodReturnsSerializerService(): void
    {
        $serializer = SerializerService::create(new SerializerConfig());

        $this->assertInstanceOf(SerializerService::class, $serializer);
    }

    public function testHydrateMethodTransformsArrayToDto(): void
    {
        $data = [
            'id' => 123,
            'dashboard_title' => 'Test Dashboard',
            'published' => true,
        ];

        $dashboard = $this->serializer->hydrate($data, Dashboard::class);

        $this->assertInstanceOf(Dashboard::class, $dashboard);
        $this->assertSame(123, $dashboard->id);
        $this->assertSame('Test Dashboard', $dashboard->title);
        $this->assertTrue($dashboard->isPublished);
    }

    public function testHydrateMethodMapsSnakeCaseToProperties(): void
    {
        $data = [
            'id' => 111,
            'dashboard_title' => 'Snake Case Test',
            'slug' => 'snake-case-test',
            'published' => false,
            'css' => '.test { color: red; }',
            'position_json' => '{"test": "position"}',
            'json_metadata' => '{"test": "metadata"}',
            'thumbnail_url' => $this->buildUrl('thumb.png'),
            'is_managed_externally' => true,
        ];

        $dashboard = $this->serializer->hydrate($data, Dashboard::class);

        $this->assertSame(111, $dashboard->id);
        $this->assertSame('Snake Case Test', $dashboard->title);
        $this->assertSame('snake-case-test', $dashboard->slug);
        $this->assertFalse($dashboard->isPublished);
        $this->assertSame('.test { color: red; }', $dashboard->css);
        $this->assertSame('{"test": "position"}', $dashboard->position);
        $this->assertSame('{"test": "metadata"}', $dashboard->metadata);
        $this->assertSame($this->buildUrl('thumb.png'), $dashboard->thumbnail);
        $this->assertTrue($dashboard->isManagedExternally);
    }

    public function testHydrateMethodHandlesDateTimeFields(): void
    {
        $data = [
            'id' => 999,
            'dashboard_title' => 'DateTime Test',
            'changed_on_utc' => '2024-01-01T10:00:00+00:00',
        ];

        $dashboard = $this->serializer->hydrate($data, Dashboard::class);

        $this->assertInstanceOf(\DateTimeImmutable::class, $dashboard->updatedAt);
        $this->assertSame('2024-01-01T10:00:00+00:00', $dashboard->updatedAt->format(\DateTime::ATOM));
    }

    public function testHydrateMethodHandlesArrayFields(): void
    {
        $data = [
            'id' => 222,
            'dashboard_title' => 'Array Test',
            'owners' => [
                ['id' => 1, 'first_name' => 'John', 'last_name' => 'Doe'],
                ['id' => 2, 'first_name' => 'Jane', 'last_name' => 'Smith'],
            ],
            'created_by' => ['id' => 3, 'first_name' => 'Alice', 'last_name' => 'Johnson'],
            'changed_by' => ['id' => 4, 'first_name' => 'Bob', 'last_name' => 'Williams'],
            'tags' => [
                ['id' => 10, 'name' => 'production', 'type' => 1],
                ['id' => 11, 'name' => 'analytics', 'type' => 2],
            ],
            'roles' => [
                ['id' => 20, 'name' => 'Admin'],
                ['id' => 21, 'name' => 'Viewer'],
            ],
        ];

        $dashboard = $this->serializer->hydrate($data, Dashboard::class);

        $this->assertCount(2, $dashboard->owners);
        $this->assertSame('John', $dashboard->owners[0]['first_name']);
        $this->assertSame('Jane', $dashboard->owners[1]['first_name']);
        $this->assertSame('Alice', $dashboard->createdBy['first_name']);
        $this->assertSame('Bob', $dashboard->updatedBy['first_name']);
        $this->assertCount(2, $dashboard->tags);
        $this->assertSame('production', $dashboard->tags[0]['name']);
        $this->assertSame('analytics', $dashboard->tags[1]['name']);
        $this->assertCount(2, $dashboard->roles);
        $this->assertSame('Admin', $dashboard->roles[0]['name']);
        $this->assertSame('Viewer', $dashboard->roles[1]['name']);
    }

    public function testHydrateMethodThrowsExceptionOnInvalidData(): void
    {
        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to hydrate data into');

        $this->serializer->hydrate(['invalid' => 'data'], Dashboard::class);
    }

    public function testDehydrateMethodTransformsDtoToArray(): void
    {
        $dashboard = new Dashboard(
            id: 456,
            title: 'Dehydrate Test',
            isPublished: false
        );

        $normalized = $this->serializer->dehydrate($dashboard);

        $this->assertIsArray($normalized);
        $this->assertSame(456, $normalized['id']);
        $this->assertSame('Dehydrate Test', $normalized['dashboard_title']);
        $this->assertFalse($normalized['published']);
    }

    public function testDehydrateMethodMapsPropertiesToSnakeCase(): void
    {
        $dashboard = new Dashboard(
            id: 333,
            title: 'Property Mapping Test',
            slug: 'property-mapping-test',
            url: $this->buildUrl('dashboard'),
            isPublished: true,
            css: '.dashboard { width: 100%; }',
            position: '{"test": "pos"}',
            metadata: '{"test": "meta"}',
            thumbnail: $this->buildUrl('image.png'),
            isManagedExternally: false
        );

        $normalized = $this->serializer->dehydrate($dashboard);

        $this->assertSame(333, $normalized['id']);
        $this->assertSame('Property Mapping Test', $normalized['dashboard_title']);
        $this->assertSame('property-mapping-test', $normalized['slug']);
        $this->assertSame($this->buildUrl('dashboard'), $normalized['url']);
        $this->assertTrue($normalized['published']);
        $this->assertSame('.dashboard { width: 100%; }', $normalized['css']);
        $this->assertSame('{"test": "pos"}', $normalized['position_json']);
        $this->assertSame('{"test": "meta"}', $normalized['json_metadata']);
        $this->assertSame($this->buildUrl('image.png'), $normalized['thumbnail_url']);
        $this->assertFalse($normalized['is_managed_externally']);
    }

    public function testDehydrateMethodHandlesDateTimeFields(): void
    {
        $updatedAt = new \DateTimeImmutable('2024-02-15T14:30:00+00:00');
        $dashboard = new Dashboard(
            id: 777,
            title: 'DateTime Dehydrate Test',
            updatedAt: $updatedAt
        );

        $normalized = $this->serializer->dehydrate($dashboard);

        $this->assertSame('2024-02-15T14:30:00+00:00', $normalized['changed_on_utc']);
    }

    public function testDehydrateMethodHandlesArrayFields(): void
    {
        $dashboard = new Dashboard(
            id: 888,
            title: 'Array Dehydrate Test',
            owners: [
                ['id' => 5, 'first_name' => 'Charlie', 'last_name' => 'Brown'],
            ],
            createdBy: ['id' => 6, 'first_name' => 'David', 'last_name' => 'Miller'],
            updatedBy: ['id' => 7, 'first_name' => 'Eve', 'last_name' => 'Davis'],
            tags: [
                ['id' => 12, 'name' => 'staging', 'type' => 3],
            ],
            roles: [
                ['id' => 22, 'name' => 'Editor'],
            ]
        );

        $normalized = $this->serializer->dehydrate($dashboard);

        $this->assertCount(1, $normalized['owners']);
        $this->assertSame('Charlie', $normalized['owners'][0]['first_name']);
        $this->assertSame('David', $normalized['created_by']['first_name']);
        $this->assertSame('Eve', $normalized['changed_by']['first_name']);
        $this->assertCount(1, $normalized['tags']);
        $this->assertSame('staging', $normalized['tags'][0]['name']);
        $this->assertCount(1, $normalized['roles']);
        $this->assertSame('Editor', $normalized['roles'][0]['name']);
    }

    public function testCreateMethodWithCustomDateTimeFormat(): void
    {
        $config = new SerializerConfig(dateTimeFormat: 'Y-m-d H:i:s');
        $serializer = SerializerService::create($config);

        $updatedAt = new \DateTimeImmutable('2024-03-20T16:45:00+00:00');
        $dashboard = new Dashboard(
            id: 555,
            title: 'Custom DateTime Format',
            updatedAt: $updatedAt
        );

        $normalized = $serializer->dehydrate($dashboard);

        $this->assertSame('2024-03-20 16:45:00', $normalized['changed_on_utc']);
    }

    public function testCreateMethodWithCustomTimeZone(): void
    {
        $config = new SerializerConfig(timeZone: 'America/New_York');
        $serializer = SerializerService::create($config);

        $updatedAt = new \DateTimeImmutable('2024-04-10T12:00:00+00:00');
        $dashboard = new Dashboard(
            id: 666,
            title: 'Custom TimeZone',
            updatedAt: $updatedAt
        );

        $normalized = $serializer->dehydrate($dashboard);

        $this->assertStringContainsString('2024-04-10T08:00:00', $normalized['changed_on_utc']);
    }

    public function testHydrateAndDehydrateRoundTrip(): void
    {
        $originalData = [
            'id' => 999,
            'dashboard_title' => 'Round Trip Test',
            'slug' => 'round-trip',
            'url' => $this->buildUrl('dashboard'),
            'published' => true,
            'css' => '.test { }',
            'position_json' => '{"x": 1}',
            'json_metadata' => '{"y": 2}',
            'thumbnail_url' => $this->buildUrl('thumb.jpg'),
            'is_managed_externally' => false,
            'changed_on_utc' => '2024-05-01T00:00:00+00:00',
        ];

        $dashboard = $this->serializer->hydrate($originalData, Dashboard::class);
        $dehydrated = $this->serializer->dehydrate($dashboard);

        $this->assertSame($originalData['id'], $dehydrated['id']);
        $this->assertSame($originalData['dashboard_title'], $dehydrated['dashboard_title']);
        $this->assertSame($originalData['slug'], $dehydrated['slug']);
        $this->assertSame($originalData['url'], $dehydrated['url']);
        $this->assertSame($originalData['published'], $dehydrated['published']);
        $this->assertSame($originalData['css'], $dehydrated['css']);
        $this->assertSame($originalData['position_json'], $dehydrated['position_json']);
        $this->assertSame($originalData['json_metadata'], $dehydrated['json_metadata']);
        $this->assertSame($originalData['thumbnail_url'], $dehydrated['thumbnail_url']);
        $this->assertSame($originalData['is_managed_externally'], $dehydrated['is_managed_externally']);
        $this->assertSame($originalData['changed_on_utc'], $dehydrated['changed_on_utc']);
    }

    public function testDehydrateMethodThrowsExceptionOnNonArrayResult(): void
    {
        /** @var Serializer&MockObject $serializer */
        $serializer = $this->createMock(Serializer::class);
        $serializer
            ->expects($this->once())
            ->method('normalize')
            ->willReturn('not-an-array');

        $service = new SerializerService($serializer);

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Expected array result but got string');

        $service->dehydrate(new Dashboard(id: 1, title: 'Test'));
    }

    public function testDehydrateMethodThrowsExceptionOnSerializerException(): void
    {
        /** @var Serializer&MockObject $serializer */
        $serializer = $this->createMock(Serializer::class);
        $exception = new class extends \RuntimeException implements ExceptionInterface {};

        $serializer
            ->expects($this->once())
            ->method('normalize')
            ->willThrowException($exception);

        $service = new SerializerService($serializer);

        $this->expectException(SerializationException::class);
        $this->expectExceptionMessage('Failed to dehydrate object of type');

        $service->dehydrate(new Dashboard(id: 1, title: 'Test'));
    }
}
