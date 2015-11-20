<?php

namespace LosMiddleware\RequestId;

use Interop\Container\ContainerInterface;

class RequestIdFactory
{
    /**
     * Creates the middleware
     *
     * @param ContainerInterface $container
     * @return \LosMiddleware\RequestId\RequestId
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $options = array_key_exists('los_request_id', $config) && !empty($config['los_request_id'])
            ? $config['los_request_id']
            : [];

        return new RequestId($options);
    }
}
