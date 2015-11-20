<?php
namespace LosMiddleware\RequestId;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

final class RequestId
{

    const HEADER_NAME = 'X-Request-Id';

    /**
     * @var array
     */
    private $options;

    public function __construct($options = [])
    {
        $this->options = array_merge([
            'uuid_version' => 4,
            'uuid_ns' => null,
            'uuid_name' => null,
            'allow_override' => false,
            'header_name' => self::HEADER_NAME,
        ], $options);

        if (!is_numeric($this->options['uuid_version']) || !in_array((int)$this->options['uuid_version'], [1,3,4,5])) {
            throw new \InvalidArgumentException("Uuid version must be 1, 3, 4 or 5");
        }
        $this->options['uuid_version'] = (int) $this->options['uuid_version'];

        if (($this->options['uuid_version'] == 3 || $this->options['uuid_version'] == 5) &&
            (empty($this->options['uuid_ns']) || empty($this->options['uuid_name']))) {
                throw new \InvalidArgumentException("Uuid versions 3 and 5 requires uuid_version and uuid_name");
        }
    }

    /**
     * Runs the middleware
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $uuid = '';

        if ($this->options['allow_override'] || !$request->hasHeader($this->options['header_name'])) {
            $uuid = $this->generateId();
            $request = $request->withHeader($this->options['header_name'], (string) $uuid);
        } elseif ($request->hasHeader($this->options['header_name'])) {
            $uuid = $request->getHeader($this->options['header_name'])[0];
        }

        $response = $next($request, $response);
        if (!empty($uuid)) {
            $response = $response->withHeader($this->options['header_name'], (string) $uuid);
        }

        return $response;
    }

    /**
     * @return string
     */
    private function generateId()
    {
        $version = $this->options['uuid_version'];
        $args = [
            $this->options['uuid_ns'],
            $this->options['uuid_name'],
        ];

        return call_user_func_array(Uuid::class . '::uuid' . $version, $args);
    }
}
