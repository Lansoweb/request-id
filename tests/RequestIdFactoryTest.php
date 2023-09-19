<?php

declare(strict_types=1);

namespace Los\RequestIdTest;

use Los\RequestId\Generator\Uuid4Generator;
use Los\RequestId\RequestId;
use Los\RequestId\RequestIdFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RequestIdFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturnOnConsecutiveCalls([], new Uuid4Generator());

        /** @psalm-suppress ArgumentTypeCoercion */
        $instance = (new RequestIdFactory())($container);
        self::assertInstanceOf(RequestId::class, $instance);
    }
}
