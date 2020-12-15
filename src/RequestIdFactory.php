<?php

declare(strict_types=1);

namespace LosMiddleware\RequestId;

use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

class RequestIdFactory
{
    public function __invoke(ContainerInterface $container): RequestId
    {
        $config = $container->get('config');
        assert(is_array($config));

        $requestConfig = $config['los_request_id'] ?? [];
        assert(is_array($requestConfig));

        return new RequestId($requestConfig);
    }
}
