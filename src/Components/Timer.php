<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Timer extends AbstractGroup
{
    public function build(array $data, array $style, array $options): void
    {
        $this->builder->dispatch(new Event('global-script', 'timer', file_get_contents(__DIR__.'/../../js/toggle.min.js')));
        parent::build($data, $style, $options);
    }
}
