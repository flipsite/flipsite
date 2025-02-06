<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Divider extends AbstractComponent
{
    protected string $tag  = 'hr';
    protected bool $empty  = true;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {

    }
}
