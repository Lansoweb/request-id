<?php

declare(strict_types=1);

namespace LosMiddleware\RequestIdTest;

use LosMiddleware\RequestId\RequestId;
use LosMiddleware\RequestId\RequestIdFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Psr\Container\ContainerInterface;

class RequestIdFactoryTest extends TestCase
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

    public function testInvoke(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn([]);

        /** @psalm-suppress ArgumentTypeCoercion */
        $instance = (new RequestIdFactory())($container);
        self::assertInstanceOf(RequestId::class, $instance);
    }
}
