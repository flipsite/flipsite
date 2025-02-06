<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Textarea extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'textarea';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $this->setContent((string)($data['value'] ?? ''));
    }
}
