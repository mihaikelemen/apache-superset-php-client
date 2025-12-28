<?php

declare(strict_types=1);

namespace Superset\Tests\Unit\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Superset\Exception\HttpResponseException;
use Superset\Exception\JsonDecodeException;
use Superset\Http\ResponseHandler;
use Superset\Tests\BaseTestCase;

#[CoversClass(ResponseHandler::class)]
#[Group('unit')]
#[Group('http')]
final class ResponseHandlerTest extends BaseTestCase
{
    private ResponseHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new ResponseHandler();
    }

    public function testHandleSuccessfulResponseWithValidJson(): void
    {
        $json = '{"status": "success", "data": {"id": 123}}';
        $httpCode = 200;

        $result = $this->handler->handle($json, $httpCode);

        $expected = ['status' => 'success', 'data' => ['id' => 123]];
        $this->assertSame($expected, $result);
    }

    public function testHandleSuccessfulResponseWithEmptyJson(): void
    {
        $json = '{}';
        $httpCode = 200;

        $result = $this->handler->handle($json, $httpCode);

        $this->assertSame([], $result);
    }

    public function testHandleSuccessfulResponseWithArrayJson(): void
    {
        $json = '[{"id": 1}, {"id": 2}]';
        $httpCode = 200;

        $result = $this->handler->handle($json, $httpCode);

        $expected = [['id' => 1], ['id' => 2]];
        $this->assertSame($expected, $result);
    }

    public function testHandleSuccessfulResponseWithNonArrayJsonThrowsException(): void
    {
        $json = '"simple string"';
        $httpCode = 200;

        $this->expectException(JsonDecodeException::class);
        $this->expectExceptionMessage('Received invalid data when decoding JSON response from Superset.');

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleInvalidJsonThrowsException(): void
    {
        $invalidJson = '{"invalid": json}';
        $httpCode = 200;

        $this->expectException(JsonDecodeException::class);
        $this->handler->handle($invalidJson, $httpCode);
    }

    public function testHandleBadRequestWithMessage(): void
    {
        $json = '{"message": "Invalid input provided"}';
        $httpCode = 400;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Superset API request failed with HTTP error 400 - Bad Request');
        $this->expectExceptionCode(400);

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleBadRequestWithErrorField(): void
    {
        $json = '{"error": "Authentication failed"}';
        $httpCode = 401;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Superset API request failed with HTTP error 401 - Unauthorized');
        $this->expectExceptionCode(401);

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleBadRequestWithoutErrorMessage(): void
    {
        $json = '{"data": "some data"}';
        $httpCode = 500;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Unexpected HTTP response from Superset.');

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleUnknownStatusCodeWithMessage(): void
    {
        $json = '{"message": "Unknown server error"}';
        $httpCode = 599;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Superset API request failed with HTTP error 599 - HTTP 599 error');
        $this->expectExceptionCode(599);

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleErrorWithNonStringMessage(): void
    {
        $json = '{"message": 123, "error": null}';
        $httpCode = 422;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Unexpected HTTP response from Superset.');

        $this->handler->handle($json, $httpCode);
    }

    public function testHandleErrorWithArrayMessage(): void
    {
        $json = '{"message": {"user": {"last_name": ["Field may not be null."]}}}';
        $httpCode = 400;

        $this->expectException(HttpResponseException::class);
        $this->expectExceptionMessage('Superset API request failed with HTTP error 400 - Bad Request');
        $this->expectExceptionCode(400);

        $this->handler->handle($json, $httpCode);
    }

    public function testIsFinalClass(): void
    {
        $reflection = new \ReflectionClass(ResponseHandler::class);

        $this->assertTrue($reflection->isFinal());
    }
}
