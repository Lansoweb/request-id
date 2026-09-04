<?php

declare(strict_types=1);

namespace Los\RequestId;

use Los\RequestId\Generator\Uuid4Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function preg_match;
use function strlen;
use function str_repeat;

final class RequestId implements MiddlewareInterface
{
    public const ATTRIBUTE_NAME = 'request_id';
    public const HEADER_NAME = 'X-Request-Id';

    private const REQUEST_ID_PATTERN = '/\A[A-Za-z0-9][A-Za-z0-9._:-]*\z/D';
    private const TRACEPARENT_PATTERN = '/\A00-([0-9a-f]{32})-([0-9a-f]{16})-[0-9a-f]{2}\z/D';

    private Options $options;
    private RequestIdGenerator $generator;

    public function __construct(?Options $options = null, ?RequestIdGenerator $generator = null)
    {
        $this->options = $options ?? new Options();
        $this->generator = $generator ?? new Uuid4Generator();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestId = $this->resolveRequestId($request);
        $request = $request
            ->withHeader($this->options->headerName(), $requestId)
            ->withAttribute($this->options->attributeName(), $requestId);

        return $handler->handle($request)->withHeader($this->options->headerName(), $requestId);
    }

    private function resolveRequestId(ServerRequestInterface $request): string
    {
        if ($this->options->acceptsInboundIds()) {
            $requestId = $this->validRequestId($request->getHeaderLine($this->options->headerName()));

            if ($requestId !== null) {
                return $requestId;
            }

            if ($this->options->usesTraceparent()) {
                $traceId = $this->traceId($request->getHeaderLine('traceparent'));

                if ($traceId !== null) {
                    return $traceId;
                }
            }
        }

        return $this->generator->generate();
    }

    private function validRequestId(string $requestId): ?string
    {
        if ($requestId === '' || strlen($requestId) > $this->options->maxIdLength()) {
            return null;
        }

        return preg_match(self::REQUEST_ID_PATTERN, $requestId) === 1 ? $requestId : null;
    }

    private function traceId(string $traceparent): ?string
    {
        if (preg_match(self::TRACEPARENT_PATTERN, $traceparent, $matches) !== 1) {
            return null;
        }

        $traceId = $matches[1];
        $parentId = $matches[2];

        if ($traceId === str_repeat('0', 32) || $parentId === str_repeat('0', 16)) {
            return null;
        }

        return $traceId;
    }
}
