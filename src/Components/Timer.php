<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;

final class Timer extends AbstractGroup
{
    use Traits\BuilderTrait;
    protected string $tag  = 'div';

    public function build(array $data, array $style, array $options): void
    {
        $this->builder->dispatch(new Event('ready-script', 'timer', file_get_contents(__DIR__.'/../../js/dist/timer.min.js')));
        parent::build($data, $style, $options);
    }
}
