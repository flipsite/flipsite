<?php

declare(strict_types=1);
namespace Flipsite\Data;

trait ComponentTypesTrait
{
    public static function getComponentType(string $componentId, &$componentData = []): string
    {
        $tmp = explode('.', $componentId);
        $tmp = explode(':', array_pop($tmp));
        if (self::isComponent($tmp[0])) {
            return $tmp[0];
        }
        if (!is_array($componentData)) {
            $componentData = ['value' => $componentData];
        }
        if ($tmp[0] === 'tagline') {
            return 'paragraph';
        }
        if (in_array($tmp[0], ['question'])) {
            return 'container';
        }
        if (in_array($tmp[0], ['logo', 'toggle'])) {
            return 'button';
        }
        return '';
    }

    public static function isComponent(string $componentType): bool
    {
        return in_array($componentType, [
            'breadcrumb',
            'button',
            'code',
            'container',
            'counter',
            'date',
            'didYouMean',
            'divider',
            'dots',
            'form',
            'gallery',
            'heading',
            'icon',
            'icons',
            'iframe',
            'image',
            'input',
            'li',
            'label',
            'languages',
            'link',
            'nav',
            'number',
            'paragraph',
            'phone',
            'polygon',
            'richtext',
            'script',
            'select',
            'social',
            'sr',
            'svg',
            'table',
            'text',
            'textarea',
            'timer',
            'ul',
            'video',
            'youtube',
        ]);
    }

    public static function isContainer(string $componentType): bool
    {
        return in_array($componentType, [
            'button',
            'container',
            'form',
            'gallery',
            'languages',
            'link',
            'nav',
            'social',
            'timer',
            'ul',
        ]);
    }
}
