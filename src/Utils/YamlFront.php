<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Symfony\Component\Yaml\Yaml;

class YamlFront
{
    /**
     * Parses a YAML file into a PHP value.
     *
     * @return array the YAML converted to a PHP value
     */
    public static function parseFile(string $filename) : bool|array
    {
        if (is_dir($filename) || !file_exists($filename)) {
            return false;
        }
        $str   = file_get_contents($filename);
        $quote = static function ($str) {
            return preg_quote($str, '~');
        };
        $regex = '~^('
            .implode('|', array_map($quote, ['---']))
            ."){1}[\r\n|\n]*(.*?)[\r\n|\n]+("
            .implode('|', array_map($quote, ['---']))
            ."){1}[\r\n|\n]*(.*)$~s";
        if (preg_match($regex, $str, $matches) === 1) {
            $yaml     = trim($matches[2]) !== '' ? Yaml::parse(trim($matches[2])) : null;
            $markdown = ltrim($matches[4]);
            return ArrayHelper::strReplace('[markdown]', $markdown, $yaml);
        }
        return false;
    }
}
