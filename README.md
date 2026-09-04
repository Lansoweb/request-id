# Request ID Middleware for PHP

los/request-id is a PHP 8.2+ PSR-15 middleware for request and log
correlation. It adds one final request ID to the request header, a request
attribute, and the response header.

## Install

    composer require los/request-id

## Usage

    use Los\RequestId\RequestId;

    $app->pipe(new RequestId());

For Laminas applications, copy config/los-request-id.global.php.dist to the
application config directory and add RequestId::class to the pipeline.

## Behavior

The middleware generates a UUID v4 unless it accepts an inbound correlation
value. The final value is available in the X-Request-Id header and the
request_id request attribute.

Inbound IDs are accepted only when they are at most 128 characters and match
[A-Za-z0-9][A-Za-z0-9._:-]*. Invalid values are replaced.

## Options

| Option | Default | Meaning |
| --- | --- | --- |
| header_name | X-Request-Id | Request and response header name. |
| attribute_name | request_id | Server-request attribute containing the final ID. |
| inbound_id_policy | accept | accept keeps valid inbound IDs; replace always generates a UUID v4. |
| max_id_length | 128 | Maximum accepted inbound ID length, from 1 to 128. |
| use_traceparent | false | Use a valid W3C traceparent version 00 trace ID when no valid request ID exists. |

When use_traceparent is enabled, a valid X-Request-Id still takes precedence.
This package only reads the trace ID for correlation; it does not operate a
distributed tracer.

## Migrating from v3

Replace allow_override with:

    'inbound_id_policy' => 'replace',

Version 4 removes configurable UUID versions. It generates UUID v4 values
when it does not accept an inbound correlation value.

## Releases

Merge a release-ready commit into the default branch, then create and push an
annotated version tag:

    git tag -a 4.0.0 -m "4.0.0"
    git push origin 4.0.0

The release workflow verifies that the tag is reachable from the default branch
and creates a GitHub Release with generated notes.
