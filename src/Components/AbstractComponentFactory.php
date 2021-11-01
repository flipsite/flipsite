<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractComponentFactory
{
    abstract public function get(string $type) : ?AbstractComponent;

    abstract public function getStyle(string $type) : array;
}
