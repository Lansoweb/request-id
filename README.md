# Request ID Middleware for PHP

This middleware adds a Request ID header that can be used to trace back requests (logs).

It uses [Ramsey\Uuid\(https://github.com/ramsey/uuid) library as default uuid generation.

## Usage

Just add the middleware as one of the first in your application.

For example:
```php
$app->pipe(new \LosMiddleware\RequestId\RequestId($options);
```

And the middleware will add a header to the request AND response
```
X-Request-Id: 56CEE969-4D3B-404E-9938-03E769E191CB
```

The options are:
* allow_override: If it's allowed to override a previouly added request id header. Default: false
* header_name: Header name. Default: X-Request-Id 

### Laminas

If you are using [laminas-skeleton](https://github.com/laminas/laminas-skeleton), you can copy `config/los-request-id.global.php.dist` to `config/autoload/los-request-id.global.php` and modify configuration as your needs.
