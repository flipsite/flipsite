<?php

declare(strict_types=1);

namespace Flipsite\Style\Parsers;

interface HtmlParserInterface
{
    public static function getElements(string $html, array $discard = []) : array;

    public static function getClasses(string $html, $discard = []) : array;
}
