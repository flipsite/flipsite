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

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $wrapperStyle = $style['wrapper'] ?? false;
        if ($wrapperStyle) {
            $this->tag = $wrapperStyle['tag'] ?? 'div';
            unset($wrapperStyle['tag']);
            $this->addStyle($wrapperStyle);
            $content = new Element($style['tag'] ?? 'div');
            $content->addStyle($style);
            unset($style['tag']);
            $this->addChild($content);
        } else {
            $this->addStyle($style);
        }
        $children = [];
        foreach ($data as $type => $componentData) {
            $componentStyle = $style[$type] ?? [];
            if (strpos($type, ':')) {
                $tmp      = explode(':', $type);
                $baseType = array_shift($tmp);
                if (isset($style[$baseType])) {
                    $componentStyle = ArrayHelper::merge($style[$baseType], $componentStyle);
                }
            }
            $children[] = $this->builder->build($type, $componentData, $componentStyle, $appearance);
        }
        if ($wrapperStyle) {
            $content->addChildren($children);
        } else {
            $this->addChildren($children);
        }
    }
}
