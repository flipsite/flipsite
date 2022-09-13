<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractGroup extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;
    use Traits\ImageHandlerTrait;
    use Traits\CanIUseTrait;
    use Traits\NthTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        if (isset($data['_index'])) {
            $nthStyle   = $this->getNth($data['_index'], $data['_total'], $style);
            $style      = ArrayHelper::merge($nthStyle, $style);
        }

        if (isset($data['onclick'])) {
            $this->setAttribute('onclick', $data['onclick']);
            if (strpos($data['onclick'], 'javascript:toggle') === 0) {
                $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.min.js')));
            }
            unset($data['onclick']);
        }

        $that         = $this; // reference to component that holds actual content, not wrappers or overlays

        $overlayStyle = $style['overlay'] ?? false;
        $wrapperStyle = $style['wrapper'] ?? false;
        unset($style['overlay'], $style['wrapper']);

        if ($this->hasBackground && $overlayStyle) {
            $overlay = new Element($overlayStyle['tag'] ?? 'div');
            unset($overlayStyle['tag']);
            $overlay->addStyle($overlayStyle);
            $this->addChild($overlay);
            $that = $overlay;
        } elseif (!$this->hasBackground && $overlayStyle) { // Overlay, no bg
            $that->addStyle($overlayStyle);
        }

        if ($wrapperStyle && $overlayStyle) {
            $wrapper = new Element($wrapperStyle['tag'] ?? 'div');
            unset($wrapperStyle['tag']);
            $wrapper->addStyle($wrapperStyle);
            $that->addChild($wrapper);
            $content = new Element($style['tag'] ?? 'div');
            unset($style['tag']);
            $wrapper->addChild($content);
            $that = $content;
        } elseif ($wrapperStyle) {
            $this->tag = $wrapperStyle['tag'] ?? 'div';
            unset($wrapperStyle['tag']);
            $that->addStyle($wrapperStyle);
            $content = new Element($style['tag'] ?? 'div');
            unset($style['tag']);
            $that->addChild($content);
            $that = $content;
        } 

        $that->addStyle($style);

        $children = [];
        $i        = 0;
        $total    = count($data);
        foreach ($data as $type => $componentData) {
            $componentStyle = $style[$type] ?? [];

            $colStyle = $this->getNth($i, $total, $style['cols'] ?? []);
            unset($colStyle['type']);
            $componentStyle = ArrayHelper::merge($componentStyle, $colStyle);

            if (strpos($type, ':')) {
                $tmp      = explode(':', $type);
                $baseType = array_shift($tmp);
                if (isset($style[$baseType])) {
                    $componentStyle = ArrayHelper::merge($style[$baseType], $componentStyle);
                }
            }
            $children[] = $this->builder->build($type, $componentData, $componentStyle, $appearance);
            $i++;
        }

        $that->addChildren($children);
    }
}
