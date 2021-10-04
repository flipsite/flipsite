<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

class Grid extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'div';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $this->tag = $data->getTag() ?? 'div';
        $type      = implode(':', $data->getFlags());
        if ($data->getStyle('colType')) {
            $type = $data->getStyle('colType');
        }

        $cols = $data->get();
        if (isset($cols['colData'])) {
            $colData = $this->addKey($cols['colData']);
            $colTpl  = $cols['colTpl'];
            $cols    = $this->mapData($colTpl, $colData);
        }

        foreach ($cols as $i => $colData) {
            $colStyle = $this->getColStyle($i, $data->getStyle());
            if ($type) {
                $components = $this->builder->build([$type => $colData], [$type => $colStyle], $data->getAppearance());
                $this->addChildren($components);
            } else {
                $containerStyle = $colStyle['container'] ?? [];
                $components     = $this->builder->build($colData, $colStyle, $data->getAppearance());
                $col            = new Element($containerStyle['tag'] ?? 'div');
                unset($containerStyle['tag']);
                $col->addStyle($containerStyle);
                $col->addChildren($components);
                $this->addChild($col);
            }
        }

        //$type = implode(':', $data->getFlags()) ?? 'group';
        // if ($type) {

        // } else {
        //     foreach ($data->getData() as $item) {
        //     //$components[] = $this->builder->build($item, [], 'light');
        //     //$this->addChildren($components);
        // }
        // }

        // foreach ($data->getData() as $item) {
        //     //$components[] = $this->builder->build($item, [], 'light');
        //     //$this->addChildren($components);
        //}

        // unset($style['container']);
        // if (isset($data['colTpl'])) {
        //     $data = $this->mapData($data['colTpl'], $data['colData']);
        // }
        // if (isset($style['colType']) || count($flags)) {
        //     $data = $this->addType($style['colType'] ?? $flags[0], $data);
        // }

        // foreach ($data as $i => $col) {
        //     $colStyle = $this->getColStyle($i, $style);
        //     print_r($colStyle);
        //     $components = [];
        //     foreach ($col as $componentType => $componentData) {
        //         $componentStyle = $colStyle[$componentType] ?? [];
        //         if (mb_strpos($componentType, ':')) {
        //             $tmp            = explode(':', $componentType);
        //             $componentStyle = ArrayHelper::merge($colStyle[$tmp[0]] ?? [], $componentStyle);
        //         }
        //         $components[] = $this->builder->build($componentType, $componentData, $componentStyle, $appearance);
        //     }
        //     if (isset($colStyle['container'])) {
        //         $col = new Element($colStyle['container']['type'] ?? 'div');
        //         unset($colStyle['container']['type']);
        //         $col->addStyle($colStyle['container']);
        //         $col->addChildren($components);
        //         $this->addChild($col);
        //     } else {
        //         $this->addChildren($components);
        //     }
        // }
    }

    private function addKey(array $data) : array
    {
        $data_ = $data;
        $data  = [];
        foreach ($data_ as $key => $value) {
            $value['key'] = $key;
            $data[]       = $value;
        }
        return $data;
    }

    private function addType(string $type, array $data) : array
    {
        $components = [];
        foreach ($data as $data_) {
            $components[] = [$type => $data_];
        }
        return $components;
    }

    private function getColStyle(int $index, array $style) : array
    {
        $all = $style['colsAll'] ?? [];
        if (0 === $index % 2) {
            $oddEven = $style['colsEven'] ?? [];
        } else {
            $oddEven = $style['colsOdd'] ?? [];
        }
        return ArrayHelper::merge($all, $oddEven, $style['cols'][$index] ?? []);
    }

    private function mapData(array $tpl, array $list) : array
    {
        $data = [];
        foreach ($list as $key => $itemData) {
            $data[] = $this->applyTpl($tpl, new \Adbar\Dot($itemData));
        }
        return $data;
    }

    private function applyTpl(array $tpl, \Adbar\Dot $data)
    {
        foreach ($tpl as $attr => &$value) {
            if (is_array($value)) {
                $value = $this->applyTpl($value, $data);
            } elseif (false !== mb_strpos((string)$value, '{colData')) {
                if ((string)$value === '{colData}') {
                    $value = $data->all();
                } else {
                    $matches = [];
                    preg_match('/\{colData\.(.*?)\}/', (string)$value, $matches);
                    $replaceWith = $data->get($matches[1]);
                    if (is_array($replaceWith)) {
                        $value = $replaceWith;
                    } else {
                        $value = str_replace($matches[0], (string)$replaceWith, (string)$value);
                    }
                }
            }
        }
        return $tpl;
    }
}
