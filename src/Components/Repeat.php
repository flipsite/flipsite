<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Repeat extends AbstractGroup
{
    use Traits\BuilderTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $repeatTpl   = $data['_repeatTpl'];
        $repeatData  = $data['_repeatData'];
        unset($data['_repeatTpl'], $data['_repeatData']);
        $children = [];
        foreach ($repeatData as $repeatDataItem) {
            foreach ($repeatTpl as $type => $repeatTplComponent) {
                if (!is_array($repeatTplComponent)) {
                    $repeatTplComponent = ['value'=>$repeatTplComponent];
                }
                $repeatTplComponent['_dataSource'] = $repeatDataItem;
                $children[] = $this->builder->build($type, $repeatTplComponent, $style[$type] ?? [], $appearance);
            }
            //     //$dataItemData['_dataSource'] = $repeatDataItem[$type] ?? [];
            //     echo $type;
            //     //$children[] = $this->builder->build($type, $dataItemData, $style[$type], $appearance);
            // }
        }
        $this->addChildren($children);

        parent::build($data, $style, $appearance);
    }

    public function normalize(string|int|bool|array $data) : array
    {
        if (isset($data['_dataSourceList'])) {
            $dataSourceList = $data['_dataSourceList'];
            unset($data['_dataSourceList']);
        }

        if (isset($data['_options']['offset']) || isset($data['_options']['length'])) {
            $offset         = $data['_options']['offset'] ?? 0;
            $length         = $data['_options']['length'] ?? 999999;
            $dataSourceList = array_splice($dataSourceList, $offset, $length);
        }
        if (isset($data['_options']['sort'])) {
            $tmp       = explode(' ', $data['_options']['sort']);
            $sortField = $tmp[0];
            uasort($dataSourceList, function ($a, $b) use ($sortField) {
                return $a[$sortField] <=> $b[$sortField];
            });
            if ('desc' === ($tmp[1] ?? 'asc')) {
                $dataSourceList = array_reverse($dataSourceList);
            }
        }
        $components = array_filter($data, function ($key) : bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatTpl']  = $components;
        $data['_repeatData'] = $dataSourceList;
        return $data;
    }
}
