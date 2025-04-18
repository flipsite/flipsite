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
        $data = $component->getData();
        if (isset($data['honeypot'])) {
            $honeypotInput = new Element('input', true, true);
            $honeypotInput->setAttribute('name', $data['honeypot']);
            $honeypotInput->setAttribute('type', 'text');
            $honeypotInput->setAttribute('tabindex', '-1');
            $honeypotInput->setAttribute('autocomplete', 'off');
            $honeypotInput->setAttribute('style', 'opacity: 0;position: absolute;top: 0;left: 0;height: 0;width: 0;z-index: -1;');
            $this->addChild($honeypotInput);
        }
        unset($data['honeypot']);
        $component->setData($data);
        parent::build($component, $inherited);
    }
}
