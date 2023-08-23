<?php

declare(strict_types=1);

namespace Flipsite\Utils;

use Symfony\Component\Yaml\Yaml;

final class SocialHelper
{
    public static array $data = [];

    public static function getData(string $type, string $handle): ?array
    {
        self::loadData();
        $data = self::$data[$type] ?? null;
        if (null === $data) {
            return null;
        }
        if (is_string($handle) || is_numeric($handle)) {
            $data['url'] = str_replace('{handle}', $handle, $data['url']);
        }
        switch ($type) {
            case 'phone':
            case 'email':
                $data['name'] = $handle;
                break;
        }
        return $data;
    }

    public static function getUrl(string $type, string $handle): string
    {
        self::loadData();
        $url = self::$data[$type]['url'];
        return str_replace('{handle}', $handle, $url);
    }

    public static function getIcon(string $type): string
    {
        self::loadData();
        return self::$data[$type]['icon'];
    }

    public static function loadData(): void
    {
        if (count(self::$data) > 0) {
            return;
        }
        $path       = __DIR__.'/social.yaml';
        self::$data = Yaml::parse(file_get_contents($path));
    }

    public static function getColors(): array
    {
        self::loadData();
        $colors = [];
        foreach (self::$data as $type => $data) {
            $colors[$type] = $data['color'];
        }
        return $colors;
    }
}
