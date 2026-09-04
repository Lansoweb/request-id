<?php

declare(strict_types=1);

namespace Los\RequestIdTest;

use InvalidArgumentException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Los\RequestId\Options;
use Los\RequestId\RequestId;
use Los\RequestId\RequestIdGenerator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function str_repeat;

final class RequestIdTest extends TestCase
{
    private const GENERATED_ID = '56cee969-4d3b-404e-9938-03e769e191cb';
    private const TRACE_ID = '4bf92f3577b34da6a3ce929d0e0e4736';

    public function testGeneratesAndPropagatesARequestId(): void
    {
        $handler = new CapturingHandler();
        $response = $this->middleware()->process(new ServerRequest(), $handler);

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine(RequestId::HEADER_NAME));
        self::assertSame(self::GENERATED_ID, $handler->request->getHeaderLine(RequestId::HEADER_NAME));
        self::assertSame(self::GENERATED_ID, $handler->request->getAttribute(RequestId::ATTRIBUTE_NAME));
    }

    public function testAcceptsAValidInboundRequestId(): void
    {
        $handler = new CapturingHandler();
        $request = (new ServerRequest())->withHeader(RequestId::HEADER_NAME, 'orders-42');
        $response = $this->middleware()->process($request, $handler);

        self::assertSame('orders-42', $response->getHeaderLine(RequestId::HEADER_NAME));
        self::assertSame('orders-42', $handler->request->getAttribute(RequestId::ATTRIBUTE_NAME));
    }

    public function testReplacesInboundRequestIdsWhenConfigured(): void
    {
        $handler = new CapturingHandler();
        $request = (new ServerRequest())->withHeader(RequestId::HEADER_NAME, 'client-request');
        $response = $this->middleware(new Options(inboundIdPolicy: 'replace'))->process($request, $handler);

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine(RequestId::HEADER_NAME));
        self::assertSame(self::GENERATED_ID, $handler->request->getAttribute(RequestId::ATTRIBUTE_NAME));
    }

    public function testReplacesInvalidAndOverlongInboundRequestIds(): void
    {
        $handler = new CapturingHandler();
        $invalidRequest = (new ServerRequest())->withHeader(RequestId::HEADER_NAME, 'contains spaces');
        $response = $this->middleware()->process($invalidRequest, $handler);

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine(RequestId::HEADER_NAME));

        $overlongRequest = (new ServerRequest())->withHeader(RequestId::HEADER_NAME, str_repeat('a', 129));
        $response = $this->middleware()->process($overlongRequest, new CapturingHandler());

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine(RequestId::HEADER_NAME));
    }

    public function testSupportsCustomHeaderAndAttributeNames(): void
    {
        $handler = new CapturingHandler();
        $response = $this->middleware(new Options('Correlation-Id', 'accept', 'correlation_id'))
            ->process(new ServerRequest(), $handler);

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine('Correlation-Id'));
        self::assertSame(self::GENERATED_ID, $handler->request->getAttribute('correlation_id'));
    }

    public function testUsesATraceparentTraceIdWhenEnabled(): void
    {
        $handler = new CapturingHandler();
        $request = (new ServerRequest())->withHeader(
            'traceparent',
            '00-' . self::TRACE_ID . '-00f067aa0ba902b7-01',
        );
        $response = $this->middleware(new Options(useTraceparent: true))->process($request, $handler);

        self::assertSame(self::TRACE_ID, $response->getHeaderLine(RequestId::HEADER_NAME));
    }

    public function testIgnoresAnInvalidTraceparent(): void
    {
        $request = (new ServerRequest())->withHeader(
            'traceparent',
            '00-00000000000000000000000000000000-00f067aa0ba902b7-01',
        );
        $response = $this->middleware(new Options(useTraceparent: true))
            ->process($request, new CapturingHandler());

        self::assertSame(self::GENERATED_ID, $response->getHeaderLine(RequestId::HEADER_NAME));
    }

    public function testInboundRequestIdTakesPrecedenceOverTraceparent(): void
    {
        $request = (new ServerRequest())
            ->withHeader(RequestId::HEADER_NAME, 'orders-42')
            ->withHeader('traceparent', '00-' . self::TRACE_ID . '-00f067aa0ba902b7-01');
        $response = $this->middleware(new Options(useTraceparent: true))
            ->process($request, new CapturingHandler());

        self::assertSame('orders-42', $response->getHeaderLine(RequestId::HEADER_NAME));
    }

    public function testRejectsInvalidOptions(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('inbound_id_policy must be "accept" or "replace"');

        new Options(inboundIdPolicy: 'preserve');
    }

    private function middleware(?Options $options = null): RequestId
    {
        return new RequestId($options, new FixedGenerator(self::GENERATED_ID));
    }
}

final class CapturingHandler implements RequestHandlerInterface
{
    public ServerRequestInterface $request;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        return new Response();
    }
}

final class FixedGenerator implements RequestIdGenerator
{
    public function __construct(private string $requestId)
    {
    }

    public function generate(): string
    {
        return $this->requestId;
    }
}
