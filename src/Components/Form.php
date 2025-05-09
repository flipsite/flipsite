<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Form extends AbstractGroup
{
    protected string $tag  = 'form';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        parent::build($component, $inherited);
    }
}
