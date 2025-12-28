<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Auth;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Auth\AuthenticationService;
use Superset\Config\ApiConfig;
use Superset\Exception\AuthenticationException;
use Superset\Http\Contracts\HttpClientInterface;
use Superset\Http\UrlBuilder;
use Superset\Service\Component\GuestUserService;
use Superset\Tests\BaseTestCase;

#[CoversClass(AuthenticationService::class)]
#[Group('unit')]
#[Group('auth')]
final class AuthenticationServiceTest extends BaseTestCase
{
    private UrlBuilder $urlBuilder;

    protected function setUp(): void
    {
        $this->urlBuilder = new UrlBuilder(self::BASE_URL, new ApiConfig());
    }

    public function testCanBeInstantiated(): void
    {
        $httpClient = $this->createStub(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $this->assertInstanceOf(AuthenticationService::class, $authService);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(AuthenticationService::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(AuthenticationService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);

        $this->assertSame('httpClient', $parameters[0]->getName());
        $this->assertSame('urlBuilder', $parameters[1]->getName());
    }

    public function testConstructorParametersAreReadonly(): void
    {
        $reflection = new \ReflectionClass(AuthenticationService::class);

        foreach (['httpClient', 'urlBuilder'] as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isReadOnly());
        }
    }

    public function testInitialStateAllTokensAreNull(): void
    {
        $httpClient = $this->createStub(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $this->assertNull($authService->getAccessToken());
        $this->assertNull($authService->getCsrfToken());
        $this->assertNull($authService->getGuestToken());
    }

    public function testIsAuthenticatedReturnsFalseInitially(): void
    {
        $httpClient = $this->createStub(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $this->assertFalse($authService->isAuthenticated());
    }

    public function testSetAccessTokenStoresToken(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $httpClient
            ->expects($this->once())
            ->method('addDefaultHeader')
            ->with('Authorization', 'Bearer test-token');

        $result = $authService->setAccessToken('test-token');

        $this->assertSame('test-token', $authService->getAccessToken());
        $this->assertSame($authService, $result);
    }

    public function testSetAccessTokenAddsAuthorizationHeader(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $httpClient
            ->expects($this->once())
            ->method('addDefaultHeader')
            ->with('Authorization', 'Bearer my-access-token');

        $authService->setAccessToken('my-access-token');
    }

    public function testIsAuthenticatedReturnsTrueAfterSettingToken(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $authService = new AuthenticationService($httpClient, $this->urlBuilder);

        $httpClient
            ->expects($this->once())
            ->method('addDefaultHeader');

        $authService->setAccessToken('some-token');

        $this->assertTrue($authService->isAuthenticated());
    }

    public function testAuthenticateCallsHttpClientWithCorrectParameters(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->buildUrl('api/v1/security/login'),
                [
                    'username' => 'testuser',
                    'password' => 'testpass',
                    'provider' => 'db',
                    'refresh' => true,
                ],
                ['Referer' => self::BASE_URL]
            )
            ->willReturn(['access_token' => 'test-access-token']);

        $httpClient
            ->expects($this->once())
            ->method('addDefaultHeader')
            ->with('Authorization', 'Bearer test-access-token');

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->authenticate('testuser', 'testpass');

        $this->assertSame('test-access-token', $authService->getAccessToken());
        $this->assertTrue($authService->isAuthenticated());
    }

    public function testAuthenticateThrowsExceptionWhenNoAccessToken(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Authentication failed: No access_token received'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->authenticate('user', 'pass');
    }

    public function testAuthenticateThrowsExceptionWhenAccessTokenNotString(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(['access_token' => 12345]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Authentication failed: No access_token received'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->authenticate('user', 'pass');
    }

    public function testRequestCsrfTokenCallsHttpClientWithCorrectParameters(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->buildUrl('api/v1/security/csrf_token/'),
                [],
                ['Referer' => self::BASE_URL]
            )
            ->willReturn(['result' => 'csrf-token-value']);

        $httpClient
            ->expects($this->once())
            ->method('addDefaultHeader')
            ->with('X-CSRFToken', 'csrf-token-value');

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $token = $authService->requestCsrfToken();

        $this->assertSame('csrf-token-value', $token);
        $this->assertSame('csrf-token-value', $authService->getCsrfToken());
    }

    public function testRequestCsrfTokenThrowsExceptionWhenNoResult(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Failed to get CSRF token'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->requestCsrfToken();
    }

    public function testRequestCsrfTokenThrowsExceptionWhenResultNotString(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('get')
            ->willReturn(['result' => 123]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Failed to get CSRF token'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->requestCsrfToken();
    }

    public function testCreateGuestTokenCallsHttpClientWithCorrectParameters(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $userAttributes = ['username' => 'jhondoe', 'first_name' => 'John'];
        $resources = ['dashboard' => 'abc-123', 'chart' => 'xyz-789'];
        $rls = [['clause' => 'user_id = 1']];

        $expectedResources = [
            ['type' => 'dashboard', 'id' => 'abc-123'],
            ['type' => 'chart', 'id' => 'xyz-789'],
        ];

        $expectedUserAttributes = [
            'first_name' => 'John',
            'last_name' => GuestUserService::GUEST_LAST_NAME,
            'username' => 'jhondoe',
        ];

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->buildUrl('api/v1/security/guest_token'),
                [
                    'resources' => $expectedResources,
                    'user' => $expectedUserAttributes,
                    'rls' => $rls,
                ],
                ['Referer' => self::BASE_URL]
            )
            ->willReturn(['token' => 'guest-token-value']);

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $token = $authService->createGuestToken($userAttributes, $resources, $rls);

        $this->assertSame('guest-token-value', $token);
        $this->assertSame('guest-token-value', $authService->getGuestToken());
    }

    public function testCreateGuestTokenWithNoGuestUserAttributesDefinedAndEmptyRls(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $resources = ['dashboard' => 'test-id'];

        $expectedUserAttributes = [
            'first_name' => GuestUserService::GUEST_FIRST_NAME,
            'last_name' => GuestUserService::GUEST_LAST_NAME,
            'username' => 'Guest_User',
        ];

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                $this->buildUrl('api/v1/security/guest_token'),
                [
                    'resources' => [['type' => 'dashboard', 'id' => 'test-id']],
                    'user' => $expectedUserAttributes,
                    'rls' => [],
                ],
                ['Referer' => self::BASE_URL]
            )
            ->willReturn(['token' => 'guest-token']);

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $token = $authService->createGuestToken([], $resources);

        $this->assertSame('guest-token', $token);
        $this->assertSame('guest-token', $authService->getGuestToken());
    }

    public function testCreateGuestTokenThrowsExceptionWhenNoToken(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn([]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Authentication failed: No token received'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->createGuestToken(['username' => 'guest'], ['dashboard' => 'id']);
    }

    public function testCreateGuestTokenThrowsExceptionWhenTokenNotString(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $httpClient
            ->expects($this->once())
            ->method('post')
            ->willReturn(['token' => null]);

        $this->expectExceptionWithMessage(
            AuthenticationException::class,
            'Authentication failed: No token received'
        );

        $authService = new AuthenticationService($httpClient, $this->urlBuilder);
        $authService->createGuestToken(['username' => 'guest'], ['dashboard' => 'id']);
    }
}
