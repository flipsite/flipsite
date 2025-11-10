<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use Ckr\Util\ArrayMerger;

final class ArrayHelper
{
    public static function getDot(array $ref, array $array)
    {
        if (1 === count($ref)) {
            return $array[array_shift($ref)] ?? null;
        }
        $next = array_shift($ref);
        if (isset($array[$next])) {
            return self::getDot($ref, $array[$next]);
        }
        return null;
    }

    public static function isAssociative(array $array): bool
    {
        if ([] === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function renameKey(array $array, string $old, string $new, bool $recursive = false): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            // Recursively rename keys in nested arrays if enabled
            if ($recursive && is_array($value)) {
                $value = self::renameKey($value, $old, $new, true);
            }

            // Rename the key while preserving order
            if ($key === $old) {
                $result[$new] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public static function merge(array ...$arrays): array
    {
        $merged = [];
        while (count($arrays)) {
            $merged = ArrayMerger::doMerge($merged, array_shift($arrays), ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY);
        }
        return $merged;
    }

    public static function unDot(array $array, string $delimiter = '.'): array
    {
        $exploded       = [];
        $renameAndEmpty = [];

        $result = [];
        foreach ($array as $attr => $val) {
            if (is_array($val)) {
                $val = self::unDot($val, $delimiter);
            }
            if (is_string($attr) && false !== mb_strpos($attr, $delimiter)) {
                $attrs   = explode($delimiter, $attr);
                $newAttr = array_shift($attrs);
                if (count($attrs)) {
                    $val = self::unDot([implode($delimiter, $attrs) => $val], $delimiter);
                }
                $val = is_array($val) ? self::unDot($val, $delimiter) : $val;

                if (isset($result[$newAttr])) {
                    $result[$newAttr] = self::merge($result[$newAttr], $val);
                } else {
                    $result[$newAttr] = $val;
                }
            } else {
                $result[$attr] = $val;
            }
        }

        return $result;
    }

    public static function strReplace(string $search, string $replace, array $array): array
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::strReplace($search, $replace, $value);
            } elseif (is_string($value)) {
                $value = str_replace($search, $replace, $value);
            }
        }
        return $array;
    }

    public static function addPrefix(array $array, string $prefix): array
    {
        $new = [];
        foreach ($array as $key => $val) {
            if (is_string($val)) {
                $val                 = $prefix . $val;
                $val                 = str_replace(' ', ' ' . $prefix, $val);
                $new[$prefix . $key] = $val;
            } elseif (is_array($val)) {
                $val       = self::addPrefix($val, $prefix);
                $new[$key] = $val;
            }
        }
        return $new;
    }

    public static function applyStringCallback(array $data, callable $callback): array
    {
        foreach ($data as $key => &$value) {
            if (is_string($value)) {
                $value = $callback($value, $key);
            } elseif (is_array($value)) {
                $value = self::applyStringCallback($value, $callback);
            }
        }
        return $data;
    }

    public static function applyArrayCallback(array $data, callable $callback): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $callback(self::applyArrayCallback($value, $callback), $key);
            }
        }
        return $data;
    }

    public static function find(string $needle, array $haystack): bool
    {
        foreach ($haystack as $item) {
            if (is_array($item)) {
                $result = self::find($needle, $item);
                if ($result !== false) {
                    return $result;
                }
            } else {
                if (strpos($item, $needle) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function decodeJsonOrCsv(mixed $string): array
    {
        if (!is_string($string)) {
            return [];
        }
        try {
            $list = json_decode($string, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $list = explode(',', $string);
        }
        return array_map('trim', $list);
    }
}
