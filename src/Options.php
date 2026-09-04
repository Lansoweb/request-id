<?php

declare(strict_types=1);

namespace Los\RequestId;

use InvalidArgumentException;

use function array_key_exists;
use function in_array;
use function is_bool;
use function is_int;
use function is_string;
use function preg_match;

final class Options
{
    private const HEADER_NAME_PATTERN = '/\A[!#$%&\'*+\-.^_\x60|~0-9A-Za-z]+\z/D';

    public function __construct(
        private string $headerName = RequestId::HEADER_NAME,
        private string $inboundIdPolicy = 'accept',
        private string $attributeName = RequestId::ATTRIBUTE_NAME,
        private int $maxIdLength = 128,
        private bool $useTraceparent = false,
    ) {
        if ($this->headerName === '' || preg_match(self::HEADER_NAME_PATTERN, $this->headerName) !== 1) {
            throw new InvalidArgumentException('Header name must be a valid HTTP field name');
        }

        if (! in_array($this->inboundIdPolicy, ['accept', 'replace'], true)) {
            throw new InvalidArgumentException('inbound_id_policy must be "accept" or "replace"');
        }

        if ($this->attributeName === '') {
            throw new InvalidArgumentException('Attribute name must be a non-empty string');
        }

        if ($this->maxIdLength < 1 || $this->maxIdLength > 128) {
            throw new InvalidArgumentException('max_id_length must be an integer between 1 and 128');
        }
    }

    /** @param array<array-key,mixed> $options */
    public static function fromArray(array $options): self
    {
        foreach ($options as $name => $_) {
            if (! is_string($name) || ! in_array($name, [
                'attribute_name',
                'header_name',
                'inbound_id_policy',
                'max_id_length',
                'use_traceparent',
            ], true)) {
                throw new InvalidArgumentException('Unknown request ID option: ' . (string) $name);
            }
        }

        return new self(
            self::stringOption($options, 'header_name', RequestId::HEADER_NAME),
            self::stringOption($options, 'inbound_id_policy', 'accept'),
            self::stringOption($options, 'attribute_name', RequestId::ATTRIBUTE_NAME),
            self::integerOption($options, 'max_id_length', 128),
            self::booleanOption($options, 'use_traceparent', false),
        );
    }

    public function headerName(): string
    {
        return $this->headerName;
    }

    public function acceptsInboundIds(): bool
    {
        return $this->inboundIdPolicy === 'accept';
    }

    public function attributeName(): string
    {
        return $this->attributeName;
    }

    public function maxIdLength(): int
    {
        return $this->maxIdLength;
    }

    public function usesTraceparent(): bool
    {
        return $this->useTraceparent;
    }

    /** @param array<array-key,mixed> $options */
    private static function stringOption(array $options, string $name, string $default): string
    {
        $value = array_key_exists($name, $options) ? $options[$name] : $default;

        if (! is_string($value)) {
            throw new InvalidArgumentException($name . ' must be a string');
        }

        return $value;
    }

    /** @param array<array-key,mixed> $options */
    private static function integerOption(array $options, string $name, int $default): int
    {
        $value = array_key_exists($name, $options) ? $options[$name] : $default;

        if (! is_int($value)) {
            throw new InvalidArgumentException($name . ' must be an integer');
        }

        return $value;
    }

    /** @param array<array-key,mixed> $options */
    private static function booleanOption(array $options, string $name, bool $default): bool
    {
        $value = array_key_exists($name, $options) ? $options[$name] : $default;

        if (! is_bool($value)) {
            throw new InvalidArgumentException($name . ' must be a boolean');
        }

        return $value;
    }
}
