<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Plugins
{
    public function __construct(private array $callbacks)
    {
    }

    public function run(string $type, $args)
    {
        if (isset($this->callbacks[$type])) {
            foreach ($this->callbacks[$type] as $callback) {
                $args = $callback($args);
            }
        }
        return $args;
    }
}
