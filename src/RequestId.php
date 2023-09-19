<?php

declare(strict_types=1);

namespace Los\RequestId;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestId implements MiddlewareInterface
{
    public const HEADER_NAME    = 'X-Request-Id';
    public const ATTRIBUTE_NAME = 'request-id';

    public function __construct(private Options $options, private RequestIdGenerator $generator)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->options->allowOverride() && $request->hasHeader($this->options->headerName())) {
            return $handler->handle($request);
        }

        $id = $this->generator->generate();

        $request = $request->withHeader($this->options->headerName(), $id);
        $request = $request->withAttribute($this->options->attributeName(), $id);

        $response = $handler->handle($request);

        return $response->withHeader($this->options->headerName(), $id);
    }
}
