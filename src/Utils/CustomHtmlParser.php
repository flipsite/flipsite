<?php

declare(strict_types=1);
namespace Flipsite\Utils;

class CustomHtmlParser
{
    private array $code = [
        'headStart' => [],
        'headEnd'   => [],
        'bodyStart' => [],
        'bodyEnd'   => [],
    ];

    public function __construct(string $customHtml)
    {
        $sections = explode('<!-- --- -->', $customHtml);
        foreach ($sections as $section) {
            $section = trim($section);
            $rows    = explode("\n", $section);

            $first = array_shift($rows);
            $first = str_replace('<!-- ', '', $first);
            $first = str_replace(' -->', '', $first);
            $tmp   = explode(' | ', $first);
            $pos   = $tmp[0];
            if (count($tmp) === 1) {
                $page = '_site';
            } else {
                $page = $tmp[1];
            }
            $code = trim(implode("\n", $rows));
            if (isset($this->code[$pos])) {
                $this->code[$pos][$page] = $code;
            }
        }
    }

    public function get(string $pos, string $page, bool $fallback = true): ?string
    {
        return $this->code[$pos][$page] ?? ($fallback ? $this->code[$pos]['_site'] ?? null : null);
    }

    public function getAll(string $page) : array
    {
        $list = [];
        foreach ($this->code as $pos => $pages) {
            foreach ($pages as $page_ => $code) {
                if ($page_ === $page) {
                    $list[] = [
                        'position' => $pos,
                        'code' => $code
                    ];
                }
            }
        }
        return $list;
    }
}
