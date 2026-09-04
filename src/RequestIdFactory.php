<?php

declare(strict_types=1);

namespace Los\RequestId;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function is_array;

final class RequestIdFactory
{
    public function __invoke(ContainerInterface $container): RequestId
    {
        $config = $container->get('config');

        if (! is_array($config)) {
            throw new InvalidArgumentException('Application config must be an array');
        }

        $losConfig = $config['los'] ?? [];

        if (! is_array($losConfig)) {
            throw new InvalidArgumentException('los config must be an array');
        }

        $requestIdConfig = $losConfig['request_id'] ?? [];

        if (! is_array($requestIdConfig)) {
            throw new InvalidArgumentException('los.request_id config must be an array');
        }

        $generator = $container->get(RequestIdGenerator::class);

        if (! $generator instanceof RequestIdGenerator) {
            throw new InvalidArgumentException(RequestIdGenerator::class . ' must implement RequestIdGenerator');
        }

        return new RequestId(Options::fromArray($requestIdConfig), $generator);
    }
}
