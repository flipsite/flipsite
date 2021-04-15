<?php

declare(strict_types=1);

namespace Flipsite\Console\Helpers;

final class DirHelper
{
    public static function getContents(string $dir, array &$results = [])
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ('.' != $value && '..' != $value) {
                self::getContents($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }
}
