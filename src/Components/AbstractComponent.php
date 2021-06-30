<?php

declare(strict_types=1);

namespace Flipsite\Components;

abstract class AbstractComponent extends AbstractElement
{
    abstract public function with(ComponentData $data) : void;
}
