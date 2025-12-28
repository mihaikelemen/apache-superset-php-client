<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Config\GuestUserConfig;
use Superset\Tests\BaseTestCase;

#[CoversClass(GuestUserConfig::class)]
#[Group('unit')]
#[Group('config')]
final class GuestUserConfigTest extends BaseTestCase
{
    public function testConstructorWithAllParameters(): void
    {
        $config = new GuestUserConfig('Alice', 'Smith', 'alice_smith');

        $this->assertSame('Alice', $config->firstName);
        $this->assertSame('Smith', $config->lastName);
        $this->assertSame('alice_smith', $config->username);
    }

    public function testConstructorWithNullValues(): void
    {
        $config = new GuestUserConfig();

        $this->assertNull($config->firstName);
        $this->assertNull($config->lastName);
        $this->assertNull($config->username);
    }

    public function testClassStructure(): void
    {
        $reflection = new \ReflectionClass(GuestUserConfig::class);

        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());

        $properties = $reflection->getProperties();
        $this->assertCount(3, $properties);

        foreach ($properties as $property) {
            $this->assertTrue($property->isPublic());
            $this->assertTrue($property->isReadOnly());
        }
    }
}
