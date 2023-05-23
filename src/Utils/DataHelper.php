<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class DataHelper
{
    public static function applyData(array $data, array $dataSource, string $dataSourceKey = '_dataSource', array $stopIfAttr = []): array
    {
        if (isset($data[$dataSourceKey])) {
            $dataSource = ArrayHelper::merge($dataSource, $data[$dataSourceKey]);
            unset($data[$dataSourceKey]);
        }
        $dataSourceDot = new \Adbar\Dot($dataSource);
        foreach ($data as $attr => &$value) {
            if (is_array($value)) {
                $attrs = array_keys($value);
                $stop  = false;
                foreach ($attrs as $attr) {
                    if (!$stop && in_array($attr, $stopIfAttr)) {
                        $stop = true;
                    }
                }
                if (!$stop) {
                    $value = self::applyData($value, $dataSource, $dataSourceKey, $stopIfAttr);
                }
            } else {
                preg_match_all('/\{([^\{\}]+)\}/', (string)$value, $matches);
                foreach ($matches[1] as $match) {
                    $replaceWith = $dataSourceDot->get($match);
                    $value       = str_replace('{'.$match.'}', (string)$replaceWith, (string)$value);
                }
            }
        }
        return $data;
    }
}
