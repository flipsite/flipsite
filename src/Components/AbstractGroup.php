<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;
    use Traits\NthTrait;

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

        $that           = $this;
        $wrapperStyle   = $style['wrapper'] ?? false;
        unset($style['wrapper']);
        if ($wrapperStyle) {
            $that->tag = $wrapperStyle['tag'] ?? 'div';
            unset($wrapperStyle['tag']);
            if (isset($data['_bgWrapper'])) {
                $wrapperStyle['background'] ??= [];
                $wrapperStyle['background']['src'] = $data['_bgWrapper'];
                unset($data['_bgWrapper']);
            }
            if (isset($wrapperStyle['background'])) {
                $this->setBackground($that, $wrapperStyle['background']);
                unset($wrapperStyle['wrapperBackground']);
            }
            $that->addStyle($wrapperStyle);
            $content = new Group($style['tag'] ?? 'div');
            $that->addChild($content);
            $that = $content;
        } else {
            $that->tag = $style['tag'] ?? $that->tag;
            unset($style['tag']);
        }
        if (isset($data['_bg'])) {
            $style['background'] ??= [];
            $style['background']['src'] = $data['_bg'];
            unset($data['_bg']);
        }
        if (isset($style['background'])) {
            $this->setBackground($that, $style['background']);
            unset($style['background']);
        }
        $that->addStyle($style);

        $children = [];
        $i        = 0;
        $total    = count($data);
        foreach ($data as $type => $componentData) {
            $componentStyle = $style[$type] ?? [];
            $children[] = $this->builder->build($type, $componentData ?? [], $componentStyle, $appearance);
            $i++;
        }

        $that->addChildren($children);
    }
}
