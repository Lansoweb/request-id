<?php
namespace LosMiddleware\RequestId;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

final class RequestId implements MiddlewareInterface
{

    const HEADER_NAME = 'X-Request-Id';

    /**
     * @var array
     */
    private $options;

    public function __construct($options = [])
    {
        $this->options = array_merge([
            'uuid' => false,
            'uuid_version' => 4,
            'uuid_ns' => null,
            'uuid_name' => null,
            'allow_override' => false,
            'header_name' => self::HEADER_NAME,
        ], $options);

        if (! is_numeric($this->options['uuid_version']) ||
            ! in_array((int)$this->options['uuid_version'], [1,3,4,5])
        ) {
            throw new \InvalidArgumentException('Uuid version must be 1, 3, 4 or 5');
        }
        $this->options['uuid_version'] = (int) $this->options['uuid_version'];

        if (($this->options['uuid_version'] == 3 || $this->options['uuid_version'] == 5) &&
            (empty($this->options['uuid_ns']) || empty($this->options['uuid_name']))) {
                throw new \InvalidArgumentException('Uuid versions 3 and 5 requires uuid_version and uuid_name');
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uuid = '';

        if ($this->options['allow_override'] || ! $request->hasHeader($this->options['header_name'])) {
            $uuid = $this->generateId();
            $request = $request->withHeader($this->options['header_name'], (string) $uuid);
        } elseif ($request->hasHeader($this->options['header_name'])) {
            $uuid = $request->getHeader($this->options['header_name'])[0];
        }

        $response = $handler->handle($request);
        if (! empty($uuid)) {
            $response = $response->withHeader($this->options['header_name'], (string) $uuid);
        }

        return $response;
    }

    /**
     * @return Uuid
     */
    private function generateId()
    {
        if ($this->options['uuid'] !== false && Uuid::isValid($this->options['uuid'])) {
            return $this->options['uuid'];
        }

        $version = $this->options['uuid_version'];
        $args = [
            $this->options['uuid_ns'],
            $this->options['uuid_name'],
        ];

        return call_user_func_array(Uuid::class . '::uuid' . $version, $args);
    }
}
