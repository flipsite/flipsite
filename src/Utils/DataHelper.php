<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class DataHelper
{
    public static function applyData(array $data, array $dataSource, string $dataSourceKey = '_dataSource', bool $replaceIfMissing = false): array
    {
        if (isset($data[$dataSourceKey]) && '_none' !== $data[$dataSourceKey]) {
            $dataSource = ArrayHelper::merge($dataSource, $data[$dataSourceKey]);
            unset($data[$dataSourceKey]);
        }

        $dataSourceDot = new \Adbar\Dot($dataSource);
        foreach ($data as &$value) {
            if (is_array($value)) {
                if (isset($value[$dataSourceKey.'List'])) {
                    if (is_array($value[$dataSourceKey.'List'])) {
                        $dataItemAttrs     = array_keys($value[$dataSourceKey.'List'][0]);
                        $stripedDataSource = [];
                        foreach ($dataSource as $attr => $val) {
                            if (!in_array($attr, $dataItemAttrs)) {
                                $stripedDataSource[$attr] = $val;
                            }
                        }
                        $value = self::applyData($value, $stripedDataSource, $dataSourceKey, false);
                    }
                    if (isset($value['_options'])) {
                        $options['_options'] = $value['_options'];
                        unset($value['_options']);
                        $options = self::applyData($options, $dataSource, $dataSourceKey, false);
                        if ($options) {
                            $value = ArrayHelper::merge($value,$options);
                        }
                    }
                } else {
                    $value = self::applyData($value, $dataSource, $dataSourceKey, $replaceIfMissing);
                }
            } else {
                preg_match_all('/\{([^\{\}]+)\}/', (string)$value, $matches);
                foreach ($matches[1] as $match) {
                    $replaceWith = $dataSourceDot->get($match);
                    if (!!$replaceWith || $replaceIfMissing) {
                        $value = str_replace('{'.$match.'}', (string)$replaceWith, (string)$value);
                    }
                }
            }
        }
        return $data;
    }
}
