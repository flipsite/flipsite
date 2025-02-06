<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Sr extends AbstractComponent
{
    protected string $tag   = 'span';
    protected bool $oneline = true;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $this->addStyle(['screenReaders' => 'sr-only']);
        $this->setContent($data['value']);
    }
}
