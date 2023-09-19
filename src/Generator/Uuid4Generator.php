<?php

declare(strict_types=1);

namespace Los\RequestId\Generator;

use Los\RequestId\RequestIdGenerator;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;

final class Uuid4Generator implements RequestIdGenerator
{
    private UuidFactoryInterface $factory;

    public function __construct(UuidFactoryInterface|null $factory = null)
    {
        $this->factory = $factory ?: new UuidFactory();
    }

    public function generate(): string
    {
        return $this->factory->uuid4()->toString();
    }
}
