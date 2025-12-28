<?php

declare(strict_types=1);

namespace Superset\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Auth\AuthenticationService;
use Superset\Config\HttpClientConfig;
use Superset\Http\Contracts\HttpClientInterface;
use Superset\Http\UrlBuilder;
use Superset\Superset;
use Superset\SupersetFactory;
use Superset\Tests\BaseTestCase;

#[CoversClass(SupersetFactory::class)]
#[Group('unit')]
#[Group('factory')]
final class SupersetFactoryTest extends BaseTestCase
{
    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(SupersetFactory::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testCreateWithMinimalParameters(): void
    {
        $client = SupersetFactory::create(self::BASE_URL);

        $this->assertInstanceOf(Superset::class, $client);
        $this->assertInstanceOf(UrlBuilder::class, $client->url());
        $this->assertInstanceOf(AuthenticationService::class, $client->auth());
    }

    public function testCreateWithCustomHttpClientConfig(): void
    {
        $httpConfig = new HttpClientConfig(self::BASE_URL);
        $client = SupersetFactory::createWithHttpClientConfig($httpConfig);

        $this->assertInstanceOf(Superset::class, $client);
    }

    public function testCreateMethodIsStatic(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'create');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
    }

    public function testCreateMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'create');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertSame('baseUrl', $parameters[0]->getName());
        $this->assertSame('httpClient', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
        $this->assertSame('logger', $parameters[2]->getName());
        $this->assertTrue($parameters[2]->allowsNull());
    }

    public function testCreateAuthenticatedReturnsAuthenticatedClient(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(['access_token' => 'test-token']);

        $client = SupersetFactory::create(self::BASE_URL, $httpClient);
        $client->auth()->authenticate('testuser', 'testpass');

        $this->assertTrue($client->auth()->isAuthenticated());
        $this->assertSame('test-token', $client->auth()->getAccessToken());
    }

    public function testCreateAuthenticatedMethodExists(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'createAuthenticated');

        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    public function testCreateAuthenticatedMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'createAuthenticated');
        $parameters = $reflection->getParameters();

        $this->assertCount(4, $parameters);
        $this->assertSame('baseUrl', $parameters[0]->getName());
        $this->assertSame('username', $parameters[1]->getName());
        $this->assertSame('password', $parameters[2]->getName());
        $this->assertSame('logger', $parameters[3]->getName());
        $this->assertTrue($parameters[3]->allowsNull());
    }

    public function testCreateAuthenticatedCallsAuthenticateMethod(): void
    {
        try {
            SupersetFactory::createAuthenticated(self::BASE_URL, 'user', 'pass');
            $this->fail('Expected an exception to be thrown');
        } catch (\Throwable $e) {
            $this->assertInstanceOf(\Throwable::class, $e);
        }
    }

    public function testCreateWithHttpClientConfigMethodExists(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'createWithHttpClientConfig');

        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }

    public function testCreateWithHttpClientConfigMethodParameters(): void
    {
        $reflection = new \ReflectionMethod(SupersetFactory::class, 'createWithHttpClientConfig');
        $parameters = $reflection->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertSame('httpConfig', $parameters[0]->getName());
        $this->assertSame('logger', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->allowsNull());
    }

    public function testCreateWithHttpClientConfigReturnsClient(): void
    {
        $httpConfig = new HttpClientConfig(self::BASE_URL);

        $client = SupersetFactory::createWithHttpClientConfig($httpConfig);

        $this->assertInstanceOf(Superset::class, $client);
    }

    public function testCreatedClientHasWorkingUrlBuilder(): void
    {
        $client = SupersetFactory::create(self::BASE_URL);
        $url = $client->url()->build('dashboard/123');

        $this->assertSame($this->buildUrl('api/v1/dashboard/123'), $url);
    }

    public function testCreatedClientHasWorkingAuthService(): void
    {
        $client = SupersetFactory::create(self::BASE_URL);

        $this->assertFalse($client->auth()->isAuthenticated());
    }

    public function testAllStaticMethodsExist(): void
    {
        $reflection = new \ReflectionClass(SupersetFactory::class);

        $createMethod = $reflection->getMethod('create');
        $createAuthenticatedMethod = $reflection->getMethod('createAuthenticated');
        $createWithHttpClientConfigMethod = $reflection->getMethod('createWithHttpClientConfig');

        $this->assertTrue($createMethod->isStatic());
        $this->assertTrue($createAuthenticatedMethod->isStatic());
        $this->assertTrue($createWithHttpClientConfigMethod->isStatic());
    }
}
