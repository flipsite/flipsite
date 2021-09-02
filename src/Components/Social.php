<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\SocialHelper;

final class Social extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\ReaderTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function with(ComponentData $data) : void
    {
        $items = $this->normalize($data->get());
        $this->addStyle($data->getStyle('container'));
        foreach ($items as $item) {
            $style      = ArrayHelper::merge($data->getStyle(), $data->getStyle($item['type']));
            $item['data']['svg'] = $item['data']['icon'];
            unset($item['data']['icon']);
            $components = $this->builder->build(['a' => $item['data']], ['a' => $style], $data->getAppearance());
            $this->addChildren($components);
        }
    }

    protected function normalize($items) : array
    {
        if (ArrayHelper::isAssociative($items)) {
            $name     = $this->reader->get('name');
            $language = $this->path->getLanguage();
            $obj      = $items;
            $items    = [];
            foreach ($obj as $type => $handle) {
                $items[] = [
                    'type' => $type,
                    'data' => SocialHelper::getData($type, (string)$handle, $name, $language),
                ];
            }
        }
        return $items;
    }
}
