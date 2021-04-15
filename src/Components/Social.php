<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\SocialHelper;

final class Social extends AbstractComponent
{
    use Traits\BuilderTrait;

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        foreach ($data as $item) {
            $a = $this->builder->build('a', $item, $style);
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener noreferrer');
            $this->addChild($a);
        }
    }

    protected function normalize($items) : array
    {
        if (ArrayHelper::isAssociative($items)) {
            $obj   = $items;
            $items = [];
            foreach ($obj as $type => $handle) {
                $item = SocialHelper::getData($type, $handle);
                unset($item['color']);

                //$item['text'] = ''

                // $args = explode('|', $url);
                // $url  = array_shift($args);
                // if (is_string($value)) {
                //     $item = [
                //         'url' => $data['url'],
                //         'icon' =>
                //     ];
                // } else {
                //     if (!isset($value['text'])) {
                //         $item = ['text' => $value];
                //     } else {
                //         $item = $value;
                //     }
                //     if (!isset($item['url'])) {
                //         $item['url'] = $url;
                //     }
                // }
                // // Inline options, e.g. |exact
                // foreach ($args as $attr) {
                //     $item[$attr] = true;
                // }
                $items[] = $item;
            }
        }
        return $items;
    }
}
