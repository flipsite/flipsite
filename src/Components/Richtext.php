<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Utils\RichtextHelper;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\YamlComponentData;
use Flipsite\Data\InheritedComponentData;

final class Richtext extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\ActionTrait;
    protected string $tag = 'div';

    public function normalize(array $data): array
    {
        $items = [];
        $data['value'] ??= '';
        try {
            if (is_string($data['value'])) {
                $json = json_decode($data['value'] ?? '', true);
                if (!$json) {
                    $json = RichtextHelper::fallbackFromString($data['value']);
                }
            } else {
                $json = $data['value'];
            }
            if (is_array($json)) {
                if (!isset($json[0]['type'])) {
                    $items = [['type' => 'ul', 'value' => $json]];
                } else {
                    foreach ($json as $item) {
                        if (($item['value'] ?? '') === '[]') {
                            continue;
                        }
                        $items[] = $item;
                    }
                }
            }
        } catch (\Exception $e) {
        }
        if (count($items) > 0) {
            $data['value'] = $items;
        } else {
            unset($data['value']);
        }
        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (!isset($data['value']) || !$data['value']) {
            $this->render = false;
            return;
        }
        $items    = $data['value'] ?? [];
        $component->purgeChildren();
        $inherited->setParent($component->getId(), $component->getType());
        foreach ($items as $index => $itemData) {
            $item = $this->getItem($itemData, $data, $component->getStyle());
            if ($item) {
                $itemComponentData  = new YamlComponentData($component->getPath(), $component->getId().'.'.$index, $item->getType(), $item->getData(), $item->getStyle());
                $component->addChild($itemComponentData);
            }
        }
        parent::build($component, $inherited);
    }

    private function getItem(array $itemData, array $componentData, array $componentStyle) : ?AbstractRichtextItem
    {
        $type = $itemData['type'] ?? null;
        switch ($type) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $tagStyle        = $componentStyle[$type] ?? [];
                $tagStyle['tag'] = $type;
                return new RichtextItemHeading($itemData['value'], $componentStyle['h'] ?? [], $tagStyle);
            case 'p':
                return new RichtextItemParagraph($itemData['value'], $componentStyle['p'] ?? [], $itemData['magiclinks'] ?? false);
            case 'img':
                return new RichtextItemImage($itemData, $componentStyle['img'] ?? []);
            case 'ul':
            case 'ol':
                return new RichtextItemList($type, $itemData, $componentData['ul']['li']['icon']['value'] ?? null, $componentData['ul']['li']['number']['format'] ?? null, $componentStyle['ul'] ?? [], $componentStyle['p'] ?? []);
            case 'table':
                return new RichtextItemTable($itemData, $componentStyle['table']);
            case 'youtube':
                return new RichtextItemYoutube($itemData, $componentStyle['youtube'] ?? []);
        }
        return null;
    }
}

class AbstractRichtextItem
{
    protected string $type;
    protected array $data;
    protected array $style;

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStyle(): array
    {
        return $this->style;
    }
}

class RichtextItemHeading extends AbstractRichtextItem
{
    protected string $type = 'heading';

    public function __construct(string $value, array $style, array $tagStyle)
    {
        $this->data['value'] = $value;
        $this->style         = ArrayHelper::merge($style, $tagStyle);
    }
}

class RichtextItemParagraph extends AbstractRichtextItem
{
    protected string $type = 'paragraph';

    public function __construct(string $value, array $style, bool $magiclinks = false)
    {
        $this->data['value'] = $value;
        if ($magiclinks) {
            $this->data['magicLinks'] = $magiclinks;
        }
        $this->style         = $style;
    }
}

class RichtextItemImage extends AbstractRichtextItem
{
    protected string $type = 'image';

    public function __construct(array $value, array $style)
    {
        $image          = [];
        $image['value'] = $value['value'];
        if (isset($value['alt'])) {
            $image['alt'] = $value['alt'];
        }

        if (isset($value['figcaption'])) {
            $this->type = 'group';
            $this->data = [
                'image'     => $image,
                'paragraph' => [
                    'value'      => $value['figcaption'],
                    '_style'     => ArrayHelper::merge($style['figcaption'] ?? [], ['tag' => 'figcaption'])
                ]
            ];

            $componentStyle = ['tag' => 'figure'];
            $unset          = [];
            $moveToWrapper  = ['margin', 'padding'];
            $copyWrapper    = ['idth', 'eight'];
            foreach ($style as $key => $val) {
                foreach ($moveToWrapper as $move) {
                    if (str_starts_with($key, $move)) {
                        $unset[]              = $key;
                        $componentStyle[$key] = $val;
                    }
                }
                foreach ($copyWrapper as $copy) {
                    if (str_ends_with($key, $copy)) {
                        $componentStyle[$key] = $val;
                    }
                }
            }
            foreach ($unset as $key) {
                unset($style[$key]);
            }
            $componentStyle['image'] = $style;
            $this->style             = $componentStyle;
        } else {
            $this->value = $image;
            $this->style = $style;
        }
    }
}

class RichtextItemList extends AbstractRichtextItem
{
    protected string $type = 'ul';

    public function __construct(string $type, array $value, ?string $icon, ?string $number, array $style)
    {
        $this->data['_repeat'] = $value['value'];
        $li                    = [];
        if ('ul' === $type && $icon) {
            $li['icon'] = $icon;
        }
        if ('ol' === $type && $number) {
            $li['number'] = [
                'format'  => $number,
                'value'   => '{index}',
                '_style'  => ArrayHelper::merge($style['li']['icon'] ?? [])
            ];
        }
        $li['value']      = '{item}';
        $this->data['li'] = $li;
        $this->style      = $style;
    }
}

class RichtextItemTable extends AbstractRichtextItem
{
    protected string $type = 'table';

    public function __construct(array $value, array $style)
    {
        $this->data  = $value;
        $this->style = $style;
    }
}

class RichtextItemYoutube extends AbstractRichtextItem
{
    protected string $type = 'youtube';

    public function __construct(array $value, array $style)
    {
        $this->data['value']    = $value['value'];
        $this->data['_attr']    = ['title' => $value['title'] ?? 'Youtube Video ('.$value['value'].')'];
        $this->data['loading']  = 'lazy';
        $this->style            = $style;
    }
}
