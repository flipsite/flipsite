<?php

declare(strict_types=1);

namespace Flipsite\Style;

interface CallbackInterface
{
    public function call(string $property, array $args) : ?string;

    public function addCallback(string $property, callable $callback) : self;
}
