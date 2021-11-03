<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractComponentFactory
{
    abstract public function get(string $type) : ?AbstractComponent;

    abstract public function getStyle(string $component) : array;

    abstract public function getLayout(string $layout) : array;
}
