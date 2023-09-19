<?php

declare(strict_types=1);

namespace Los\RequestId;

interface RequestIdGenerator
{
    public function generate(): string;
}
