<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Builders\ComponentBuilder;

trait BuilderTrait
{
    protected ComponentBuilder $builder;

    public function addBuilder(ComponentBuilder $builder) : void
    {
        $this->builder = $builder;
    }
}
