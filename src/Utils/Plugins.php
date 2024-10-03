<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class Plugins
{
    public function __construct(private array $callbacks)
    {
    }

    public function has(string $type): bool
    {
        return isset($this->callbacks[$type]);
    }

    public function run(string $type, $data, ...$args)
    {
        if (isset($this->callbacks[$type])) {
            foreach ($this->callbacks[$type] as $callback) {
                $data = $callback($data, ...$args);
            }
        }
        return $data;
    }
}
