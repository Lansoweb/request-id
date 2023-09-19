<?php

declare(strict_types=1);

namespace Los\RequestId;

final class Options
{
    public function __construct(
        private string $headerName = RequestId::HEADER_NAME,
        private bool $allowOverride = false,
        private string $attributeName = RequestId::ATTRIBUTE_NAME,
    ) {
    }

    public function headerName(): string
    {
        return $this->headerName;
    }

    public function allowOverride(): bool
    {
        return $this->allowOverride;
    }

    public function attributeName(): string
    {
        return $this->attributeName;
    }
}
