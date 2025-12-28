<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Enum\HttpStatusCode;
use Superset\Tests\BaseTestCase;

#[CoversClass(HttpStatusCode::class)]
#[Group('unit')]
#[Group('enum')]
final class HttpStatusCodeTest extends BaseTestCase
{
    public function testIsBackedEnum(): void
    {
        $reflection = new \ReflectionEnum(HttpStatusCode::class);

        $this->assertTrue($reflection->isBacked());
        $this->assertSame('int', $reflection->getBackingType()->getName());
    }

    public function testHttpUnknownCase(): void
    {
        $this->assertSame(0, HttpStatusCode::HTTP_UNKNOWN->value);
        $this->assertSame('Unknown', HttpStatusCode::HTTP_UNKNOWN->message());
    }

    public function testHttpOkCase(): void
    {
        $this->assertSame(200, HttpStatusCode::HTTP_OK->value);
        $this->assertSame('OK', HttpStatusCode::HTTP_OK->message());
    }

    public function testHttpCreatedCase(): void
    {
        $this->assertSame(201, HttpStatusCode::HTTP_CREATED->value);
        $this->assertSame('Created', HttpStatusCode::HTTP_CREATED->message());
    }

    public function testHttpAcceptedCase(): void
    {
        $this->assertSame(202, HttpStatusCode::HTTP_ACCEPTED->value);
        $this->assertSame('Accepted', HttpStatusCode::HTTP_ACCEPTED->message());
    }

    public function testHttpNoContentCase(): void
    {
        $this->assertSame(204, HttpStatusCode::HTTP_NO_CONTENT->value);
        $this->assertSame('No Content', HttpStatusCode::HTTP_NO_CONTENT->message());
    }

    public function testHttpBadRequestCase(): void
    {
        $this->assertSame(400, HttpStatusCode::HTTP_BAD_REQUEST->value);
        $this->assertSame('Bad Request', HttpStatusCode::HTTP_BAD_REQUEST->message());
    }

    public function testHttpUnauthorizedCase(): void
    {
        $this->assertSame(401, HttpStatusCode::HTTP_UNAUTHORIZED->value);
        $this->assertSame('Unauthorized', HttpStatusCode::HTTP_UNAUTHORIZED->message());
    }

    public function testHttpForbiddenCase(): void
    {
        $this->assertSame(403, HttpStatusCode::HTTP_FORBIDDEN->value);
        $this->assertSame('Forbidden', HttpStatusCode::HTTP_FORBIDDEN->message());
    }

    public function testHttpNotFoundCase(): void
    {
        $this->assertSame(404, HttpStatusCode::HTTP_NOT_FOUND->value);
        $this->assertSame('Not Found', HttpStatusCode::HTTP_NOT_FOUND->message());
    }

    public function testHttpMethodNotAllowedCase(): void
    {
        $this->assertSame(405, HttpStatusCode::HTTP_METHOD_NOT_ALLOWED->value);
        $this->assertSame('Method Not Allowed', HttpStatusCode::HTTP_METHOD_NOT_ALLOWED->message());
    }

    public function testHttpConflictCase(): void
    {
        $this->assertSame(409, HttpStatusCode::HTTP_CONFLICT->value);
        $this->assertSame('Conflict', HttpStatusCode::HTTP_CONFLICT->message());
    }

    public function testHttpGoneCase(): void
    {
        $this->assertSame(410, HttpStatusCode::HTTP_GONE->value);
        $this->assertSame('Gone', HttpStatusCode::HTTP_GONE->message());
    }

    public function testHttpPreconditionFailedCase(): void
    {
        $this->assertSame(412, HttpStatusCode::HTTP_PRECONDITION_FAILED->value);
        $this->assertSame('Precondition Failed', HttpStatusCode::HTTP_PRECONDITION_FAILED->message());
    }

    public function testHttpInternalServerErrorCase(): void
    {
        $this->assertSame(500, HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR->value);
        $this->assertSame('Internal Server Error', HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR->message());
    }

    public function testHttpServiceUnavailableCase(): void
    {
        $this->assertSame(503, HttpStatusCode::HTTP_SERVICE_UNAVAILABLE->value);
        $this->assertSame('Service Unavailable', HttpStatusCode::HTTP_SERVICE_UNAVAILABLE->message());
    }

    public function testAllCasesHaveUniqueValues(): void
    {
        $cases = HttpStatusCode::cases();
        $values = array_map(fn (HttpStatusCode $case) => $case->value, $cases);

        $this->assertSame(count($values), count(array_unique($values)));
    }

    public function testMessageMethodExistsAndIsPublic(): void
    {
        $reflection = new \ReflectionEnum(HttpStatusCode::class);
        $method = $reflection->getMethod('message');

        $this->assertTrue($method->isPublic());
        $this->assertSame('string', $method->getReturnType()->getName());
    }
}
