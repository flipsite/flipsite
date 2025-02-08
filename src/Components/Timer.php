<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Timer extends AbstractGroup
{
    use Traits\BuilderTrait;
    protected string $tag  = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $this->builder->dispatch(new Event('ready-script', 'timer', file_get_contents(__DIR__.'/../../js/dist/timer.min.js')));
        parent::build($component, $inherited);
    }
}
