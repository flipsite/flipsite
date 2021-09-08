<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use Ckr\Util\ArrayMerger;
use Symfony\Component\Yaml\Yaml;

class YamlDumper
{
    public static function dump($input, int $inline = 2, int $indent = 4, int $flags = 0): string
    {
        $yaml = Yaml::dump($input, $inline, $indent, $flags);

        $matches = [];
        preg_match_all("/\s*\\'{1}[a-zA-Z:]+\\'{1}\:/", $yaml, $matches);

        foreach ($matches[0] as $match) {
            $with = str_replace("'", '', $match);
            $yaml = str_replace($match, $with, $yaml);
        }
        preg_match_all("/\:\s{1}\'[a-zA-Z]{1}.*\'/", $yaml, $matches);

        foreach ($matches[0] as $match) {
            if (strpos($match, "''") === false) {
                $with = ltrim($match, ": '");
                $with = rtrim($with, "'");
                $yaml = str_replace($match, ': '.$with, $yaml);
            }
        }
        return $yaml;
    }
}
