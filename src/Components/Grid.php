<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

final class Grid extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\UrlTrait;

    protected string $tag = 'div';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $this->tag = $data->getTag() ?? 'div';
        $type      = implode(':', $data->getFlags());

        foreach ($data->get() as $i => $colData) {
            $colStyle = $this->getColStyle($i, $data->getStyle());
            if ($type) {
                $components = $this->builder->build([$type => $colData], [$type => $colStyle], $data->getAppearance());
                $this->addChildren($components);
            } else {
                $containerStyle = $colStyle['container'] ?? [];
                unset($colStyle['container']);
                $components = $this->builder->build($colData, $colStyle, $data->getAppearance());
                $col        = new Element('div');
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
            $itemData['key'] = $key;
            $data[]          = $this->applyTpl($tpl, new \Adbar\Dot($itemData));
        }
        return $data;
    }

    private function applyTpl(array $tpl, \Adbar\Dot $data)
    {
        foreach ($tpl as $attr => &$value) {
            if (is_array($value)) {
                $value = $this->applyTpl($value, $data);
            } elseif (false !== mb_strpos($value, '{colData.')) {
                $matches = [];
                preg_match('/\{colData\.(.*?)\}/', $value, $matches);
                $value = str_replace($matches[0], $data->get($matches[1]), $value);
            }
        }
        return $tpl;
    }
}
