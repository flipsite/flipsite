<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Richtext extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\ActionTrait;
    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            $data = ['value' => (string)$data];
        }
        $items = [];
        try {
            $json = json_decode($data['value'] ?? '', true);
            if (is_array($json)) {
                foreach ($json as $item) {
                    $items[] = new RichtextItem($item, $data['liIcon'] ?? null, $data['liNumber'] ?? null);
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

    public function build(array $data, array $style, array $options): void
    {
        if (!($data['value'] ?? false)) {
            return;
        }

        $items = $data['value'] ?? [];
        unset($data['value']);
        foreach ($items as $index => $item) {
            $componentId = $item->getComponentType().':'.$index;
            $data[$componentId] = $item->getData($style);
            $data[$componentId]['_style'] = $item->getStyle($style);
        }
        parent::build($data, $style, $options);
    }
}

class RichtextItem
{
    private $type;
    private $data;
    public function __construct(array $rawData, private ?array $icon = null, private ?array $number = null)
    {
        $this->type = $rawData['type'] ?? '';
        $this->data = $rawData['data'] ?? '';
    }
    public function getComponentType(): ?string
    {
        switch ($this->type) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
                return 'heading';
            case 'p':
                return 'paragraph';
            case 'img':
                return 'image';
            case 'ol':
            case 'ul':
                return 'ul';
        }
        return null;
    }
    public function getData(array $allStyle): string|array
    {
        $markdown = [
            'a'      => $allStyle['a'] ?? [],
            'strong' => $allStyle['strong'] ?? [],
            'em' => $allStyle['em'] ?? [],
            'code' => $allStyle['code'] ?? [],
        ];
        switch ($this->type) {
            case 'ol':
                return [
                    '_repeat' => $this->data,
                    'li' => [
                        'number' => $this->number ?
                        ArrayHelper::merge($this->number, ['value' => '{index}', '_style' => $allStyle['liNumber'] ?? []]) : null,
                        'value' => '{item}',
                        '_style' => ArrayHelper::merge($allStyle['li'] ?? [], $markdown)
                    ],

                ];
            case 'ul':
                return [
                    '_repeat' => $this->data,
                    'li' => [
                        'icon' => $this->icon ? ArrayHelper::merge($this->icon ?? [], ['_style' => $allStyle['liIcon'] ?? []]) : null,
                        'value' => '{item}',
                        '_style' => ArrayHelper::merge($allStyle['li'] ?? [], $markdown)
                    ],

                ];
        }
        return ['value' => $this->data];
    }
    public function getStyle(array $allStyle): array
    {
        $componentStyle = [];
        switch ($this->type) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
                $componentStyle = ['tag' => $this->type];
                break;
            case 'p':
                $componentStyle = [
                    'a'      => $allStyle['a'] ?? [],
                    'strong' => $allStyle['strong'] ?? [],
                    'em' => $allStyle['em'] ?? [],
                    'code' => $allStyle['code'] ?? [],
                ];
                break;
            case 'ul':
                $componentStyle = ['tag' => $this->type];
                if (!$this->icon) {
                    $componentStyle['listStylePosition'] = 'list-inside';
                    $componentStyle['listStyleType'] = 'list-disc';
                }
                break;
            case 'ol':
                $componentStyle = ['tag' => $this->type];
                break;
        }
        return ArrayHelper::merge($allStyle[$this->type] ?? [], $componentStyle);
    }
}
