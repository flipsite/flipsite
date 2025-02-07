<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Code extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $tag  = 'pre';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $this->oneline = true;
        $this->setContent($data['value']);
        $style['self'] = 'code';
        $this->addStyle($style);

        $this->builder->dispatch(new Event('ready-script', 'highlight.js', file_get_contents(__DIR__.'/../../js/dist/highlight.min.js')));
        if (isset($data['theme'])) {
            $this->setAttribute('data-hljs-theme', $data['theme']);
        }
    }
}
