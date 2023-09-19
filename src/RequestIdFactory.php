<?php

declare(strict_types=1);

namespace Los\RequestId;

use Psr\Container\ContainerInterface;

use function assert;
use function is_array;

class RequestIdFactory
{
    public function __invoke(ContainerInterface $container): RequestId
    {
        $config = $container->get('config');
        assert(is_array($config));

        /** @param array{
         *     allow_override?: bool,
         *     header_name?: string
         * } $options
         */
        $requestConfig = $config['los']['request_id'] ?? [];
        assert(is_array($requestConfig));

        $generator = $container->get(RequestIdGenerator::class);
        assert($generator instanceof RequestIdGenerator);

        return new RequestId(
            new Options(
                $requestConfig['header_name'] ?? '',
                (bool) ($requestConfig['allow_override'] ?? false),
            ),
            $generator,
        );
    }
}
