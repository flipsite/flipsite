<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Counter extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $tag  = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        $this->builder->dispatch(new \Flipsite\Builders\Event('ready-script', 'counter', file_get_contents(__DIR__.'/../../js/dist/counter.min.js')));
        $this->setAttribute('data-counter', true);
        $this->setAttribute('data-timing', (string)($data['timing'] ?? 'ease-in-out'));
        $this->setAttribute('data-to', (string)($data['to'] ?? 100));
        $this->setAttribute('data-duration', (string)($data['duration'] ?? 500));
        $value = new Element('span', true);
        $value->setContent((string)($data['from'] ?? 0));
        $span = trim($value->render());
        if (isset($data['content'])) {
            $this->setContent(str_replace('[counter]', $span, $data['content']));
        } else {
            $this->setContent($span);
        }
    }
}
