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
                    $items[] = new RichtextItem($item);
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

    public function build(array $data, array $style, array $options): void
    {
        if (!($data['value'] ?? false)) {
            return;
        }
        $items = $data['value'] ?? [];
        unset($data['value']);
        foreach ($items as $index => $item) {
            $componentId = $item->getComponentType().':'.$index;
            $data[$componentId] = $item->getData();
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
    public function __construct(array $rawData)
    {
        $this->type = $rawData['type'] ?? '';
        $this->data = $rawData['data'] ?? '';
    }
    public function getComponentType(): string
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
        }
    }
    public function getData(): string|array
    {
        return ['value' => $this->data];
    }
    public function getStyle($allStyle): array
    {
        $componentStyle = [];
        switch ($this->getComponentType()) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
                $componentStyle = ['tag' => $this->type];
                break;
            case 'p':
                break;
        }
        return ArrayHelper::merge($allStyle[$this->type] ?? [], $componentStyle);
    }
}
