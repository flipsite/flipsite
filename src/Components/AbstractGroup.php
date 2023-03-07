<?php

declare(strict_types=1);
namespace Flipsite\Components;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\StyleOptimizerTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        foreach ($data['_attr'] ?? [] as $attr => $val) {
            $this->setAttribute($attr, $val);
        }
        unset($data['_attr']);
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
            $componentStyle = $this->optimizeStyle($style[$type] ?? [], $i, $total);
            $children[]     = $this->builder->build($type, $componentData ?? [], $componentStyle, $appearance);
            $i++;
        }

        $this->addChildren($children);
    }
}
