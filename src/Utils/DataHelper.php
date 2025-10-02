<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class DataHelper
{
    public static function applyData(array $data, array $dataSource, array &$replaced, callable|null $filter = null): array
    {
        foreach ($data as $key => &$value) {
            if (null !== $filter && $filter($key)) {
            } elseif (is_string($value) && strpos($value, '{') !== false) {
                // Dont apply data on JSON
                if (str_starts_with($value, '[{"')) {
                    $json = json_decode($value, true);
                    if (is_array($json)) {
                        continue;
                    }
                }
                $matches = [];
                preg_match_all('/\{[^{}]+\}/', $value, $matches);
                $original = $value;
                foreach ($matches[0] as $match) {
                    $var   = trim($match, '{}');
                    if (isset($dataSource[$var])) {
                        $value      = str_replace($match, (string)$dataSource[$var], (string)$value);
                        $replaced[] = $match;
                    } elseif ($match === $value) {
                        $value = null;
                    }
                }
                if ($original !== $value) {
                    $data['_original'] ??= [];
                    $data['_original'][$key] = $original;
                };
            } elseif (is_array($value) && in_array($key, ['_attr', '_options', 'render'])) {
                $value = self::applyData($value, $dataSource, $replaced, $filter);
            }
        }
        return $data;
    }

    public static function applyDataToBranch(array $data, array $dataSource): array
    {
        foreach ($data as $key => &$value) {
            if (is_string($value) && strpos($value, '{') !== false) {
                // Dont apply data on JSON
                if (str_starts_with($value, '[{"')) {
                    $json = json_decode($value, true);
                    if (is_array($json)) {
                        continue;
                    }
                }
                $matches = [];
                preg_match_all('/\{[^{}]+\}/', $value, $matches);
                $original = $value;
                foreach ($matches[0] as $match) {
                    $var   = trim($match, '{}');
                    if (isset($dataSource[$var])) {
                        $value      = str_replace($match, (string)$dataSource[$var], (string)$value);
                        $replaced[] = $match;
                    } elseif ($match === $value) {
                        $value = null;
                    }
                }
            } elseif (is_array($value)) {
                $value = self::applyDataToBranch($value, $dataSource);
            }
        }
        return $data;
    }
}
