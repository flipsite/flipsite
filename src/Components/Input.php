<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Input extends AbstractComponent
{
    protected bool $oneline = true;
    protected bool $empty   = true;
    protected string $tag   = 'input';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
    }
}
