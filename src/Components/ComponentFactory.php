<?php

declare(strict_types=1);

namespace Flipsite\Components;

class ComponentFactory extends AbstractComponentFactory
{
    public function get(string $type) : ?AbstractComponent
    {
        switch ($type) {
            case 'plain':
            case 'text':
                return new PlainText();
            case 'mdline':
                return new MdLine();
            case 'image':
                return new Picture();
            case 'icon':
                return new Svg();
            default:
                $class = 'Flipsite\\Components\\'.ucfirst($type);
                if (class_exists($class)) {
                    return new $class();
                }
        }
        if ($this->isTag($type)) {
            return new Tag($type);
        }
        return null;
    }

    private function isTag(string $tag) : bool
    {
        return in_array($tag, [
            'p',
            'span',
            'div',
            'small',
            'cite',
        ]);
    }
}
