<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Symfony\Component\Yaml\Yaml;

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
            case 'markdown':
                return new Md();
            case 'image':
                return new Image();
            case 'picture':
                return new Picture();
            case 'icon':
            case 'svg':
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
            'label',
            'span',
            'div',
            'small',
            'blockquote',
            'cite',
        ]);
    }

    // public function getStyle(string $component) : array
    // {
    //     $filePath = __DIR__.'/../../yaml/components/'.$component.'.yaml';
    //     if (file_exists($filePath)) {
    //         return Yaml::parseFile($filePath) ?? [];
    //     }
    //     return [];
    // }

    // public function getLayout(string $layout) : array
    // {
    //     $filePath = __DIR__.'/../../yaml/layouts/'.$layout.'.yaml';
    //     if (file_exists($filePath)) {
    //         return Yaml::parseFile($filePath) ?? [];
    //     }
    //     return [];
    // }
}
