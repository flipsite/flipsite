<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\SocialHelper;

final class Social extends AbstractItems
{
    use Traits\BuilderTrait;
    use Traits\ReaderTrait;
    use Traits\PathTrait;

    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!ArrayHelper::isAssociative($data)) {
            $data['items'] = $data;
            return $data;
        }

        $name     = $this->reader->get('name');
        $language = $this->path->getLanguage();
        $items    = [];
        $i        = 0;

        foreach ($data as $type => $handle) {
            if (!is_array($handle)) {
                $item = SocialHelper::getData($type, (string)$handle, $name, $language);
                unset($item['color'], $item['name']);
                $items[] = $item;
            }
        }
        $data['items'] = $items;
        return $data;
    }
}
