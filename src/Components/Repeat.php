<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Repeat extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\StyleOptimizerTrait;

    protected string $tag = 'div';

    public function build(array $data, array $style, string $appearance) : void
    {
        $repeatTpl   = $data['_repeatTpl'];
        $repeatData  = $data['_repeatData'];
        unset($data['_repeatTpl'], $data['_repeatData']);
        $children = [];
        $total    = count($repeatData);

        foreach ($repeatData as $i => $repeatDataItem) {
            foreach ($repeatTpl as $type => $repeatTplComponent) {
                if (!is_array($repeatTplComponent)) {
                    $repeatTplComponent = ['value'=>$repeatTplComponent];
                }
                $repeatTplComponent['_dataSource'] = $repeatDataItem;
                $optimizedStyle = $this->optimizeStyle($style[$type] ?? [], $i, $total);
                if (isset($optimizedStyle['background'])) {
                    $optimizedStyle['background'] = $this->optimizeStyle($optimizedStyle['background'], $i, $total);
                }
                $children[] = $this->builder->build($type, $repeatTplComponent, $optimizedStyle, $appearance);
            }
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
            $offset         = intval($data['_options']['offset'] ?? 0);
            $length         = intval($data['_options']['length'] ?? 999999);
            $dataSourceList = array_splice($dataSourceList, $offset, $length);
        }

        error_log($data['_options']['filter']);
        error_log($data['_options']['filterBy']);

        if (isset($data['_options']['sortBy'])) {
            $sortField = $data['_options']['sortBy'];
            uasort($dataSourceList, function ($a, $b) use ($sortField) {
                if (isset($a[$sortField],$b[$sortField])) {
                    return $a[$sortField] <=> $b[$sortField];
                }
                return 0;
            });
        }
        if (isset($data['_options']['sort']) && 'desc' === $data['_options']['sort']) {
            $dataSourceList = array_reverse($dataSourceList);
        }
        $components = array_filter($data, function ($key) : bool {
            return !str_starts_with($key, '_');
        }, ARRAY_FILTER_USE_KEY);
        foreach (array_keys($components) as $key) {
            unset($data[$key]);
        }
        $data['_repeatTpl']  = $components;
        $data['_repeatData'] = $dataSourceList ?? [];
        return $data;
    }
}

