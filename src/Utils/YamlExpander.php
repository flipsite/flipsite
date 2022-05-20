<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Ckr\Util\ArrayMerger;
use Symfony\Component\Yaml\Yaml;

class YamlExpander
{
    public static array $cache = [];

    /**
     * Parses a YAML file into a PHP value.
     *
     * @return array the YAML converted to a PHP value
     */
    public static function parseFile(string $filename)
    {
        // Parse YAML
        $data = Yaml::parseFile($filename);

        // Parse included files
        $pathinfo = pathinfo($filename);
        $dir      = $pathinfo['dirname'];
        $data     = self::parseIncludes($data, $dir);

        // Undot data
        $data = self::unDot($data);

        // Parse references (both string and object)
        $data = self::parseRef($data, $data);

        // Merge extend
        return self::mergeExtend($data);
    }

    public static function parseIncludes(array $data, string $rootDir) : array
    {
        foreach ($data as $attr => &$val) {
            if (is_array($val)) {
                $val = self::parseIncludes($val, $rootDir);
            } elseif (is_string($val) && str_starts_with($val, '$')) {
                $filepath = $rootDir.'/'.ltrim($val, '$');
                if (is_dir($filepath)) {
                    $name = ltrim($val, '$');
                    if (isset(self::$cache[$name])) {
                        $val = self::$cache[$name];
                    } else {
                        $list = [];
                        $i    = 0;
                        foreach (scandir($filepath) as $file) {
                            $parsed = YamlFront::parseFile($filepath.'/'.$file);
                            if ($parsed) {
                                $item                         = $parsed;
                                $list[$parsed['key'] ?? $i++] = $parsed;
                            } elseif (!str_starts_with($file, '.')) {
                                $list[$parsed['key'] ?? $i++] = $file;
                            }
                        }
                        self::$cache[$name] = $list;
                        $val                = self::$cache[$name];
                    }
                } elseif (file_exists($filepath)) {
                    $pathinfo = pathinfo($filepath);
                    $val      = Yaml::parseFile($filepath);
                    $dir      = $pathinfo['dirname'];
                    $val      = self::parseIncludes($val, $dir);
                    $val      = self::parseRef($val, $val);
                }
            }
        }
        return $data;
    }

    public static function parseRef(array $data, array $original) : array
    {
        $pattern = '/\$\{([^\$}]+)\}/';
        foreach ($data as $attr => &$val) {
            if (is_array($val)) {
                $val = self::parseRef($val, $original);
            } elseif (is_string($val)) {
                preg_match_all($pattern, $val, $matches);
                foreach ($matches[1] as $i => $ref) {
                    $new = self::getDot(explode('.', $ref), $original);

                    if (is_array($new)) {
                        $val = self::parseRef($new, $original);
                    } elseif (is_string($new)) {
                        $val = str_replace($matches[0][$i], $new, $val);
                    }
                }
            }
        }
        return $data;
    }

    public static function mergeExtend(array $data) : array
    {
        $attr = '$'.'extend';
        if (isset($data[$attr])) {
            $extend = $data[$attr];
            unset($data[$attr]);
            if (self::isAssociative($extend)) {
                $extend = [$extend];
            }
            $extend[] = $data;
            return self::mergeExtend(self::merge(...$extend));
        }
        foreach ($data as $attr => &$val) {
            if (is_array($val)) {
                $val = self::mergeExtend($val);
            }
        }
        return $data;
    }

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

    public static function renameKey(array $array, string $old, string $new) : array
    {
        $keys = array_keys($array);
        if (false === $index = array_search($old, $keys, true)) {
            throw new \Exception(sprintf('Key "%s" does not exist', $old));
        }
        $keys[$index] = $new;
        return array_combine($keys, array_values($array));
    }

    public static function merge(array ...$arrays) : array
    {
        $merged = [];
        while (count($arrays)) {
            $merged = ArrayMerger::doMerge($merged, array_shift($arrays), \Ckr\Util\ArrayMerger::FLAG_OVERWRITE_NUMERIC_KEY);
        }
        return $merged;
    }

    public static function unDot(array $array, string $delimiter = '.') : array
    {
        $exploded       = [];
        $renameAndEmpty = [];

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
                if (isset($exploded[$newAttr])) {
                    $exploded[$newAttr] = self::merge($exploded[$newAttr], $val);
                    unset($array[$attr]);
                } else {
                    $exploded[$newAttr]    = $val;
                    $renameAndEmpty[$attr] = $newAttr;
                }
            } else {
                $array[$attr] = $val;
            }
        }
        if (count($exploded)) {
            foreach ($renameAndEmpty as $old => $new) {
                $existing    = $array[$new] ?? [];
                $array       = self::renameKey($array, $old, $new);
                $array[$new] = self::merge($existing, $exploded[$new]);
                unset($exploded[$new]);
            }
        }
        return $array;
    }

    public static function isAssociative(array $array) : bool
    {
        if ([] === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
