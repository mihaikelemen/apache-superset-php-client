<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Service\Component;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\GuestUserConfig;
use Superset\Service\Component\GuestUserService;
use Superset\Tests\BaseTestCase;

#[CoversClass(GuestUserService::class)]
#[Group('unit')]
#[Group('service')]
final class GuestUserServiceTest extends BaseTestCase
{
    public function testConstructorWithDefaultValues(): void
    {
        $service = new GuestUserService();

        $expected = [
            'first_name' => GuestUserService::GUEST_FIRST_NAME,
            'last_name' => GuestUserService::GUEST_LAST_NAME,
            'username' => 'Guest_User',
        ];

        $this->assertSame($expected, $service->attributes());
    }

    public function testConstructorWithAllParameters(): void
    {
        $config = new GuestUserConfig('Alice', 'Smith', 'alice_smith');
        $service = new GuestUserService($config);

        $expected = [
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'username' => 'alice_smith',
        ];

        $this->assertSame($expected, $service->attributes());
    }

    public function testEmptyValuesUseDefaults(): void
    {
        $config = new GuestUserConfig('', '', '');
        $service = new GuestUserService($config);

        $expected = [
            'first_name' => GuestUserService::GUEST_FIRST_NAME,
            'last_name' => GuestUserService::GUEST_LAST_NAME,
            'username' => 'Guest_User',
        ];

        $this->assertSame($expected, $service->attributes());
    }

    public function testWhitespaceValuesArePreserved(): void
    {
        $config = new GuestUserConfig('   ', '   ', '   ');
        $service = new GuestUserService($config);

        $this->assertSame('   ', $service->attributes()['first_name']);
        $this->assertSame('   ', $service->attributes()['last_name']);
        $this->assertSame('   ', $service->attributes()['username']);
    }

    public function testCustomUsernameOverridesGenerated(): void
    {
        $config = new GuestUserConfig('John', 'Doe', 'custom_user');
        $service = new GuestUserService($config);

        $this->assertSame('custom_user', $service->attributes()['username']);
    }

    public function testGeneratedUsernameWithWhitespace(): void
    {
        $config = new GuestUserConfig('John  Michael', 'Doe  Smith');
        $service = new GuestUserService($config);

        $username = $service->attributes()['username'];

        $this->assertStringNotContainsString('  ', $username);
        $this->assertMatchesRegularExpression('/^John_Michael_Doe_Smith$/', $username);
    }

    public function testPregReplaceFailureFallback(): void
    {
        $initialValue = \ini_get('pcre.backtrack_limit');

        try {
            \ini_set('pcre.backtrack_limit', '1');

            $config = new GuestUserConfig(
                \str_repeat('A', 1000),
                \str_repeat('B', 1000)
            );
            $service = new GuestUserService($config);

            $username = $service->attributes()['username'];
            $this->assertIsString($username);
            $this->assertNotEmpty($username);
        } finally {
            \ini_set('pcre.backtrack_limit', $initialValue);
        }
    }

    public function testStaticFromMethodWithNull(): void
    {
        $expected = [
            'first_name' => GuestUserService::GUEST_FIRST_NAME,
            'last_name' => GuestUserService::GUEST_LAST_NAME,
            'username' => 'Guest_User',
        ];

        $this->assertSame($expected, GuestUserService::from(null));
    }

    public function testClassStructure(): void
    {
        $reflection = new \ReflectionClass(GuestUserService::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());

        $configProperty = $reflection->getProperty('config');
        $this->assertTrue($configProperty->isPrivate());
        $this->assertTrue($configProperty->isReadOnly());

        $this->assertTrue($reflection->getMethod('attributes')->isPublic());
        $this->assertTrue($reflection->getMethod('from')->isPublic());
        $this->assertTrue($reflection->getMethod('from')->isStatic());
        $this->assertTrue($reflection->getMethod('getFirstName')->isPrivate());
        $this->assertTrue($reflection->getMethod('getLastName')->isPrivate());
        $this->assertTrue($reflection->getMethod('getUsername')->isPrivate());
    }
}
