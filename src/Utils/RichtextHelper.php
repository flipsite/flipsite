<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class RichtextHelper
{
    public static function fallbackFromString(string $string): array
    {
        $items = explode('</p>', $string);
        $json  = [];
        foreach ($items as $item) {
            $val = trim(strip_tags($item));
            if ($val) {
                $json[] = [
                    'type'   => 'p',
                    'value'  => $val
                ];
            }
        }
        return $json;
    }
    public static function toPlainText(mixed $encodedRichtext): mixed
    {
        if (!is_string($encodedRichtext) || !self::isRichtext($encodedRichtext)) {
            return $encodedRichtext;
        }
        $rows = [];
        foreach (json_decode($encodedRichtext, true) as $block) {
            $type = $block['type'] ?? '';
            switch ($type) {
                case 'p': $rows[] = $block['value'] ?? '';
                    break;
                case 'h1': $rows[] = '#'.$block['value'] ?? '';
                    break;
                case 'h2': $rows[] = '##'.$block['value'] ?? '';
                    break;
                case 'h3': $rows[] = '###'.$block['value'] ?? '';
                    break;
                case 'h4': $rows[] = '####'.$block['value'] ?? '';
                    break;
                case 'h5': $rows[] = '#####'.$block['value'] ?? '';
                    break;
                case 'h6': $rows[] = '######'.$block['value'] ?? '';
                    break;
                case 'ul':
                case 'ol':
                    if (isset($block['items']) && is_array($block['items'])) {
                        foreach ($block['items'] as $item) {
                            $rows[] = '- ' . ($item['value'] ?? '');
                        }
                    }
                    break;
                case 'pre':
                    $rows[] = $block['code'] ?? '';
                    break;
            }
        }
        return implode("\n", $rows);
    }
    public static function isRichtext(string $encodedRichtext): bool
    {
        return str_starts_with($encodedRichtext, '[{"type"') && str_ends_with($encodedRichtext, '}]');
    }
}
