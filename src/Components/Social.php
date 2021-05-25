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

    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        foreach ($data as $item) {
            $a = $this->builder->build('a', $item['data'], $style);
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener noreferrer');
            $a->addStyle($style[$item['type']] ?? []);
            $this->addChild($a);
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
                    'data' => SocialHelper::getData($type, $handle, $name, $language),
                ];
            }
        }
        return $items;
    }
}
