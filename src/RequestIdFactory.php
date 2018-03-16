<?php

namespace LosMiddleware\RequestId;

use Psr\Container\ContainerInterface;

class RequestIdFactory
{
    /**
     * Creates the middleware
     *
     * @param ContainerInterface $container
     * @return \LosMiddleware\RequestId\RequestId
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new RequestId($config['los_request_id'] ?? []);
    }
}
