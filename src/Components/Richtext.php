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
                foreach ($json as $item) {
                    if (($item['value'] ?? '') === '[]') {
                        continue;
                    }
                    $items[] = new RichtextItem($item, $data['liIcon'] ?? null, $data['liNumber'] ?? null, $data['codeBlock']['theme'] ?? null);
                }
            }
        } catch (\Exception $e) {
        }
        if (count($items) > 0) {
            $data['value'] = $items;
        } else {
            unset($data['value']);
        }
        unset($data['liIcon']);
        return $data;
    }

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (!isset($data['value']) || !$data['value']) {
            $this->render = false;
            return;
        }

        $style = $component->getStyle();

        $items = $data['value'] ?? [];
        unset($data['value']);

        $inherited->setParent($component->getId(), $component->getType());
        foreach ($items as $index => $item) {
            $itemComponentData  = new YamlComponentData($component->getPath(), $component->getId().'.'.$index, $item->getType(), $item->getData($style), $item->getStyle($style));
            $component->addChild($itemComponentData);
        }
        parent::build($component, $inherited);
    }
}

class RichtextItem
{
    private string $type;
    private array $data;

    public function __construct(array $rawData, private ?array $icon = null, private ?array $number = null, private ?string $theme = null)
    {
        $this->type = $rawData['type'] ?? '';
        unset($rawData['type']);
        $this->data = $rawData;
    }

    public function getType(): ?string
    {
        switch ($this->type) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                return 'heading';
            case 'p':
                return 'paragraph';
            case 'img':
                if (isset($this->data['figcaption'])) {
                    return 'group';
                }
                return 'image';
            case 'ol':
            case 'ul':
                return 'ul';
        }
        return $this->type;
    }

    public function getData(array $allStyle): string|array
    {
        $markdown = [
            'a'      => $allStyle['a'] ?? [],
            'strong' => $allStyle['strong'] ?? [],
            'em'     => $allStyle['em'] ?? [],
            'code'   => $allStyle['code'] ?? [],
        ];
        switch ($this->type) {
            case 'ol':
                return [
                    '_repeat' => $this->data['value'],
                    'li'      => [
                        'number'     => $this->number ?
                        ArrayHelper::merge($this->number, ['value' => '{index}', '_style' => $allStyle['liNumber'] ?? []]) : null,
                        'value'   => '{item}',
                        '_style'  => ArrayHelper::merge($allStyle['li'] ?? [], $markdown),
                    ],

                ];
            case 'ul':
                return [
                    '_repeat' => $this->data['value'],
                    '_meta'   => [
                        'componentChildId'   => 'ul',
                        'componentChildName' => 'Lists'
                    ],
                    'li'      => [
                        'icon'       => $this->icon ? ArrayHelper::merge($this->icon ?? [], ['_style' => $allStyle['liIcon'] ?? []]) : null,
                        'value'      => '{item}',
                        '_style'     => ArrayHelper::merge($allStyle['li'] ?? [], $markdown),
                    ],

                ];
            case 'img':
                $image          = ['value' => $this->data['value'] ?? null, 'alt' => $this->data['alt'] ?? null];
                if (isset($this->data['figcaption'])) {
                    return [
                        'image'     => $image,
                        'paragraph' => [
                            'value'      => $this->data['figcaption'],
                            '_style'     => ArrayHelper::merge($allStyle['figcaption'] ?? [], ['tag' => 'figcaption'])]
                    ];
                }
                return $image;
            case 'youtube':
                $data  = $this->data;
                $title = $data['title'] ?? 'Youtube Video';
                unset($data['title']);
                $data['_attr']   = ['title' => $title];
                $data['loading'] = 'lazy';
                return $data;
            case 'codeBlock':
                $data = $this->data;
                if ($this->theme) {
                    $data['theme'] = $this->theme;
                }
                return $data;
        }
        return $this->data;
    }

    public function getStyle(array $allStyle): array
    {
        $componentStyle = [];
        switch ($this->type) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $componentStyle = ['tag' => $this->type];
                break;
            case 'p':
                $componentStyle = [
                    'a'      => $allStyle['a'] ?? [],
                    'strong' => $allStyle['strong'] ?? [],
                    'em'     => $allStyle['em'] ?? [],
                    'code'   => $allStyle['code'] ?? [],
                ];
                break;
            case 'ul':
                $componentStyle = ['tag' => $this->type];
                if (!$this->icon) {
                    $componentStyle['listStylePosition'] = 'list-inside';
                    $componentStyle['listStyleType']     = 'list-disc';
                }
                break;
            case 'ol':
                $componentStyle = ['tag' => $this->type];
                break;
            case 'img':
                if (isset($this->data['figcaption'])) {
                    $imageStyle     = $allStyle[$this->type] ?? [];
                    $componentStyle = ['tag' => 'figure'];
                    $unset          = [];
                    $moveToWrapper  = ['margin', 'padding'];
                    $copyWrapper    = ['idth', 'eight'];
                    foreach ($imageStyle as $key => $val) {
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
                        unset($imageStyle[$key]);
                    }
                    $componentStyle['image'] = $imageStyle;
                    return $componentStyle;
                }
                break;
            case 'table':
                $componentStyle = [
                    'th'      => $allStyle['th'] ?? [],
                    'tr'      => $allStyle['tr'] ?? [],
                    'td'      => $allStyle['td'] ?? [],
                    'a'       => $allStyle['a'] ?? [],
                    'strong'  => $allStyle['strong'] ?? [],
                    'em'      => $allStyle['em'] ?? [],
                    'code'    => $allStyle['code'] ?? []
                ];
        }
        return ArrayHelper::merge($allStyle[$this->type] ?? [], $componentStyle);
    }
}
