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
        $hasDataHref = false;
        foreach ($cols as $i => $colData) {
            $colStyle = $this->getColStyle($i, $data->getStyle());
            if ($type) {
                $components = $this->builder->build([$type => $colData], [$type => $colStyle], $data->getAppearance());
                $this->addChildren($components);
            } else {
                $containerStyle = $colStyle['container'] ?? [];
                $wrapper        = null;
                if (isset($containerStyle['wrapper'])) {
                    $wrapper = new Element('div');
                    $wrapper->addStyle($containerStyle['wrapper']);
                    unset($containerStyle['wrapper']);
                }
                $dataHref = $colData['url'] ?? null;
                unset($colData['url']);
                if ($dataHref) {
                    $hasDataHref = true;
                    $external    = false;
                    $dataHref    = $this->url($dataHref, $external);
                }
                $components     = $this->builder->build($colData, $colStyle, $data->getAppearance());
                $col            = new Element($containerStyle['tag'] ?? 'div');
                unset($containerStyle['tag']);
                $col->addStyle($containerStyle);
                $col->addChildren($components);
                if ($wrapper) {
                    $wrapper->addChild($col);
                    $wrapper->setAttribute('data-href', $dataHref);
                    $this->addChild($wrapper);
                } else {
                    $col->setAttribute('data-href', $dataHref);
                    $this->addChild($col);
                }
            }
        }
        if ($hasDataHref) {
            $this->builder->dispatch(new Event('ready-script', 'data-href', file_get_contents(__DIR__.'/../../js/ready.data-href.js')));
        }
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
