<?php

declare(strict_types=1);

namespace LosMiddleware\RequestId;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

use function array_merge;
use function assert;
use function in_array;
use function is_int;
use function is_string;

final class RequestId implements MiddlewareInterface
{
    public const HEADER_NAME = 'X-Request-Id';

    /** @var array<array-key,mixed> */
    private array $options;

    /** @param array<array-key,mixed> $options */
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'uuid'           => false,
            'uuid_version'   => 4,
            'uuid_ns'        => null,
            'uuid_name'      => null,
            'allow_override' => false,
            'header_name'    => self::HEADER_NAME,
        ], $options);

        if (
            ! is_int($this->options['uuid_version']) ||
            ! in_array($this->options['uuid_version'], [1, 3, 4, 5, 6])
        ) {
            throw new InvalidArgumentException('Uuid version must be 1, 3, 4, 5 or 6');
        }

        if (
            ($this->options['uuid_version'] === 3 || $this->options['uuid_version'] === 5) &&
            (empty($this->options['uuid_ns']) || empty($this->options['uuid_name']))
        ) {
            throw new InvalidArgumentException('Uuid versions 3 and 5 requires uuid_ns and uuid_name');
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uuid = '';

        $headerName = $this->options['header_name'];
        assert(is_string($headerName));

        if ($this->options['allow_override'] || ! $request->hasHeader($headerName)) {
            $uuid    = $this->generateId();
            $request = $request->withHeader($headerName, (string) $uuid);
        } elseif ($request->hasHeader($headerName)) {
            $uuid = $request->getHeader($headerName)[0];
        }

        $response = $handler->handle($request);

        if (! empty($uuid)) {
            $response = $response->withHeader($headerName, (string) $uuid);
        }

        return $response;
    }

    private function generateId(): UuidInterface
    {
        if (is_string($this->options['uuid']) && Uuid::isValid($this->options['uuid'])) {
            return Uuid::fromString($this->options['uuid']);
        }

        $version = (int) $this->options['uuid_version'];

        switch ($version) {
            case 1:
                return Uuid::uuid1();

            case 3:
            case 5:
                $ns   = $this->options['uuid_ns'];
                $name = $this->options['uuid_name'];
                assert(is_string($ns));
                assert(is_string($name));

                if ($version === 3) {
                    return Uuid::uuid3($ns, $name);
                }

                return Uuid::uuid5($ns, $name);

            case 6:
                return Uuid::uuid6();

            default:
                return Uuid::uuid4();
        }
    }
}
