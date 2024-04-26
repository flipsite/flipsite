<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class FileHelper
{
    public static function getMostRecentlyModifiedFile(string $dir): array
    {
        $latestFile = '';
        $latestModificationTime = 0;

        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item != '.' && $item != '..' && !self::startsWith($item, '.')) {
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    $recentFile = self::getMostRecentlyModifiedFile($path);
                    if ($recentFile['modificationTime'] > $latestModificationTime) {
                        $latestFile = $recentFile['file'];
                        $latestModificationTime = $recentFile['modificationTime'];
                    }
                } else {
                    $modificationTime = filemtime($path);
                    if ($modificationTime > $latestModificationTime) {
                        $latestFile = $path;
                        $latestModificationTime = $modificationTime;
                    }
                }
            }
        }

        return ['file' => $latestFile, 'modificationTime' => $latestModificationTime];
    }

    private static function startsWith(string $string, string $prefix): bool
    {
        return substr($string, 0, strlen($prefix)) === $prefix;
    }
}
