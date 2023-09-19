<?php

declare(strict_types=1);

namespace Los\RequestIdTest;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Los\RequestId\Generator\Uuid4Generator;
use Los\RequestId\Options;
use Los\RequestId\RequestId;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Rfc4122\Validator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;

class RequestIdTest extends TestCase
{
    public function testConstruct(): void
    {
        $requestId = new RequestId(new Options(), new Uuid4Generator());
        self::assertInstanceOf(RequestId::class, $requestId);
    }

    public function testId(): void
    {
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };

        $requestId = new RequestId(new Options(), new Uuid4Generator());
        $response  = $requestId->process(new ServerRequest(), $handler);

        $factory = new UuidFactory();
        $factory->setValidator(new Validator());
        Uuid::setFactory($factory);
        $uuid = Uuid::fromString($response->getHeader(RequestId::HEADER_NAME)[0]);

        self::assertInstanceOf(UuidV4::class, $uuid);
    }
}
