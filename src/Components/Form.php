<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Form extends AbstractGroup
{
    protected string $tag  = 'form';

    public function build(array $data, array $style, array $options): void
    {
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
        parent::build($data, $style, $options);
    }
}
