<?php

declare(strict_types=1);

namespace LosMiddleware\RequestIdTest;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use LosMiddleware\RequestId\RequestId;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Rfc4122\UuidV1;
use Ramsey\Uuid\Rfc4122\UuidV3;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Rfc4122\UuidV5;
use Ramsey\Uuid\Rfc4122\Validator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

use function preg_match;

class RequestIdTest extends TestCase
{
    private Prophet $prophet;

    protected function setUp(): void
    {
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->prophet->checkPredictions();
    }

    public function testValidVersion(): void
    {
        self::expectExceptionMessage('Uuid version must be 1, 3, 4, 5 or 6');
        new RequestId(['uuid' => true, 'uuid_version' => 0]);
    }

    public function testV3orV5WithOptions(): void
    {
        self::expectExceptionMessage('Uuid versions 3 and 5 requires uuid_ns and uuid_name');
        new RequestId(['uuid' => true, 'uuid_version' => 3]);
    }

    public function testConstruct(): void
    {
        $requestId = new RequestId();
        self::assertInstanceOf(RequestId::class, $requestId);
    }

    public function testV1(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId(['uuid' => true, 'uuid_version' => 1]);
        $response  = $requestId->process(new ServerRequest(), $handler);

        $factory = new UuidFactory();
        $factory->setValidator(new Validator());
        Uuid::setFactory($factory);
        $uuid = Uuid::fromString($response->getHeader(RequestId::HEADER_NAME)[0]);

        self::assertInstanceOf(UuidV1::class, $uuid);
    }

    public function testV3(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId([
            'uuid'         => true,
            'uuid_version' => 3,
            'uuid_ns'      => '22c5ab64-8b9f-45c0-ab03-22fa24c833fa',
            'uuid_name'    => 'name',
        ]);
        $response  = $requestId->process(new ServerRequest(), $handler);

        $factory = new UuidFactory();
        $factory->setValidator(new Validator());
        Uuid::setFactory($factory);
        $uuid = Uuid::fromString($response->getHeader(RequestId::HEADER_NAME)[0]);

        self::assertInstanceOf(UuidV3::class, $uuid);
    }

    public function testV4(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId(['uuid' => true, 'uuid_version' => 4]);
        $response  = $requestId->process(new ServerRequest(), $handler);

        $factory = new UuidFactory();
        $factory->setValidator(new Validator());
        Uuid::setFactory($factory);
        $uuid = Uuid::fromString($response->getHeader(RequestId::HEADER_NAME)[0]);

        self::assertInstanceOf(UuidV4::class, $uuid);
    }

    public function testV5(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId([
            'uuid'         => true,
            'uuid_version' => 5,
            'uuid_ns'      => '22c5ab64-8b9f-45c0-ab03-22fa24c833fa',
            'uuid_name'    => 'name',
        ]);
        $response  = $requestId->process(new ServerRequest(), $handler);

        $factory = new UuidFactory();
        $factory->setValidator(new Validator());
        Uuid::setFactory($factory);
        $uuid = Uuid::fromString($response->getHeader(RequestId::HEADER_NAME)[0]);

        self::assertInstanceOf(UuidV5::class, $uuid);
    }

    public function testV6(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId(['uuid' => true, 'uuid_version' => 6]);
        $response  = $requestId->process(new ServerRequest(), $handler);

        self::assertEquals(1, preg_match(
            '/\A[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-6{1}[0-9A-Fa-f]{3}-[ABab89]{1}[0-9A-Fa-f]{3}-[0-9A-Fa-f]{12}\z/Dms',
            $response->getHeader(RequestId::HEADER_NAME)[0]
        ));
    }
}
