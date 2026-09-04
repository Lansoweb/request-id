<?php

declare(strict_types=1);

namespace Los\RequestIdTest;

use InvalidArgumentException;
use Los\RequestId\RequestId;
use Los\RequestId\RequestIdFactory;
use Los\RequestId\RequestIdGenerator;
use PHPUnit\Framework\TestCase;

final class RequestIdFactoryTest extends TestCase
{
    public function testUsesDefaultOptionsWhenNoPackageConfigExists(): void
    {
        self::assertInstanceOf(RequestId::class, (new RequestIdFactory())(
            new ConfigContainer([], new FactoryGenerator()),
        ));
    }

    public function testRejectsInvalidPackageConfig(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('los.request_id config must be an array');

        (new RequestIdFactory())(new ConfigContainer(['los' => ['request_id' => 'invalid']], new FactoryGenerator()));
    }
}

final class ConfigContainer implements \Psr\Container\ContainerInterface
{
    public function __construct(private mixed $config, private RequestIdGenerator $generator)
    {
    }

    public function get(string $id): mixed
    {
        return $id === 'config' ? $this->config : $this->generator;
    }

    public function has(string $id): bool
    {
        return $id === 'config' || $id === RequestIdGenerator::class;
    }
}

final class FactoryGenerator implements RequestIdGenerator
{
    public function generate(): string
    {
        return 'generated-id';
    }
}
