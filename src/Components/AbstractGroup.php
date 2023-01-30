<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        // Hmm
        if (isset($data['onclick'])) {
            $this->setAttribute('onclick', $data['onclick']);
            if (strpos($data['onclick'], 'javascript:toggle') === 0) {
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.min.js')));
            }
            unset($data['onclick']);
        }

        $this->tag ??= $style['tag'];
        unset($style['tag']);

        if (isset($data['_bg'])) {
            $style['background'] ??= [];
            $style['background']['src'] = $data['_bg'];
            unset($data['_bg']);
        }
        if (isset($style['background'])) {
            $this->setBackground($this, $style['background']);
            unset($style['background']);
        }
        $this->addStyle($style);

        $children = [];
        $i        = 0;
        $total    = count($data);
        foreach ($data as $type => $componentData) {
            $componentStyle = $style[$type] ?? [];
            $children[]     = $this->builder->build($type, $componentData ?? [], $componentStyle, $appearance);
            $i++;
        }

        $this->addChildren($children);
    }
}
