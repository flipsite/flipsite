<?php

declare(strict_types=1);

namespace Flipsite\Style\Parsers;

final class HtmlParser implements HtmlParserInterface
{
    public static function getElements(string $html, array $discard = []) : array
    {
        $matches = [];
        preg_match_all('/<(?:[a-z1-6]+)(>|\s)/', $html, $matches);
        $elements = [];
        foreach ($matches[0] as $element) {
            $element = str_replace('<', '', $element);
            $element = str_replace('>', '', $element);
            $element = trim($element);
            if (!in_array($element, $discard) && !in_array($element, $elements)) {
                $elements[] = $element;
            }
        }
        return $elements;
    }

    public static function getClasses(string $html, $discard = []) : array
    {
        $matches = [];
        $html    = str_replace("\n", ' ', $html);
        preg_match_all('/(?:class)=(?:["\']\W+\s*(?:\w+)\()?["\']([^\'"]+)[\'"]/', $html, $matches);
        $classes = [];
        foreach ($matches[1] as $classString) {
            foreach (explode(' ', $classString) as $class) {
                $class = trim($class);
                if (in_array($class, $discard)) {
                    continue;
                }
                if (mb_strlen($class) && !in_array($class, $classes)) {
                    $classes[] = $class;
                }
            }
        }
        return array_unique($classes);
    }
}
