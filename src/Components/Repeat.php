<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Repeat extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\RepeatTrait;
    use Traits\NthTrait;

    protected string $tag = 'div';

    public function normalize(string|int|bool|array $data) : array
    {
        if (isset($data['dataSourceList'])) {
            $dataSourceList = $data['dataSourceList'];
            unset($data['dataSourceList']);
        }

        if (isset($data['options']['offset']) || isset($data['options']['length'])) {
            $offset       = $data['options']['offset'] ?? 0;
            $length       = $data['options']['length'] ?? 99999;
            $dataSourceList = array_splice($dataSourceList['cols'], $offset, $length);
        }
        if (isset($data['options']['sort'])) {
            $tmp       = explode(' ', $data['options']['sort']);
            $sortField = $tmp[0];
            uasort($dataSourceList, function ($a, $b) use ($sortField) {
                return $a[$sortField] <=> $b[$sortField];
            });
            if ('desc' === ($tmp[1] ?? 'asc')) {
                $dataSourceList = array_reverse($dataSourceList['cols']);
            }
        }

        
        
        // $keys = array_keys($data);
        // if (in_array('col',$keys)) {
        //     if (null === ($data['cols'] ?? null)) {
        //         $this->render = false;
        //     }
        //     foreach ($data['cols'] as $index => &$col) {
        //         $dataSource = $col ?? [];
        //         $col = $data['col'];
        //         $col['dataSource'] = $dataSource;
        //     }
        //     unset($data['col']);
        // }
        return $data;
    }
}
