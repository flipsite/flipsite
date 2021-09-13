<?php

declare(strict_types=1);

namespace Flipsite\Sections;

abstract class AbstractSectionFactory
{
    abstract public function getStyle(string $section) : ?array;
    abstract public function getExample(string $section) : ?array;
}
