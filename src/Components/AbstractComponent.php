<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

abstract class AbstractComponent extends AbstractElement
{
    abstract public function build(AbstractComponentData $component, InheritedComponentData $inherited): void;

    public function normalize(array $data): array
    {
        return $data;
    }
}
