<?php

declare(strict_types=1);

namespace Flipsite\Console;

final class Commands
{
    /**
     * @return array<string>
     */
    public static function getCommands($dir, &$results = []) : array
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if (!is_dir($path)) {
                $pathinfo = pathinfo($path);
                if (false !== mb_strpos($pathinfo['basename'], 'Command.php')) {
                    $dirs      = explode('/', $pathinfo['dirname']);
                    $type      = array_pop($dirs);
                    $results[] = 'Flipsite\Console\Commands\\'.$type.'\\'.$pathinfo['filename'];
                }
            } elseif ('.' !== $value && '..' !== $value) {
                self::getCommands($path, $results);
            }
        }
        return $results;
    }
}
