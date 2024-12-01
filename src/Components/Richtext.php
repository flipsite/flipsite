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
                    $items[] = new RichtextItem($item, $data['liIcon'] ?? null);
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
        /*foreach ($data['value'] as $index => $component) {
            $tag         = $component['tag'];
            $componentId = null;
            switch ($tag) {
                case 'h1':
                case 'h2':
                case 'h3':
                case 'h4':
                case 'h5':
                case 'h6':
                    $data['heading:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag])
                    ];
                    break;
                case 'ul':
                case 'ol':
                    $data['ul:'.$index] = [

                        '_repeat'   => $component['value'],
                        '_style'    => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag]),
                        'paragraph' => [
                            'value'  => '{item}',
                            '_style' => ArrayHelper::merge(['tag' => 'li'], [
                                'a'      => $style['a'] ?? [],
                                'strong' => $style['strong'] ?? [],
                            ])
                        ]
                    ];
                    break;
                case 'img':
                    $data['image:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge($style[$tag] ?? [], ['tag' => $tag])
                    ];
                    break;
                case 'p':
                    $data['paragraph:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => ArrayHelper::merge(['tag' => $tag], [
                            'a'      => $style['a'] ?? [],
                            'strong' => $style['strong'] ?? [],
                        ])
                    ];
                    break;
                case 'table':
                    $data['table:'.$index] = [
                        'th'      => $component['th'] ?? [],
                        'td'      => $component['td'] ?? [],
                        '_style'  => ArrayHelper::merge($style['tbl'] ?? [], [
                            'th' => $style['th'] ?? [],
                            'td' => $style['td'] ?? [],
                        ])
                    ];
                    break;
                case 'youtube':
                    $data['youtube:'.$index] = [
                        'value'  => $component['value'],
                        '_style' => $style['youtube'] ?? [],
                    ];
                    break;
            }
        }
            */
        parent::build($data, $style, $options);
    }
}

class RichtextItem
{
    private $type;
    private $data;
    public function __construct(array $rawData, private ?array $icon = null)
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
        switch ($this->type) {
            case 'ol':
            case 'ul':
                return [
                    '_repeat' => $this->data,
                    'li' => [
                        'icon' => ArrayHelper::merge($this->icon ?? [], ['_style' => $allStyle['liIcon'] ?? []]),
                        'value' => '{item}',
                        '_style' => $allStyle['li']
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
            case 'ol':
                $componentStyle = ['tag' => $this->type];
                break;
        }
        return ArrayHelper::merge($allStyle[$this->type] ?? [], $componentStyle);
    }
}
