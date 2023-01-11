<?php

declare(strict_types=1);

namespace Flipsite\Utils;

class CustomHtmlParser
{
    private array $headStart = [];
    private array $headEnd = [];
    private array $bodyStart = [];
    private array $bodyEnd = [];

    public function __construct(string $customHtml)
    {
        $sections = explode('<!-- --- -->', $customHtml);
        foreach ($sections as $section) {
            $section = trim($section);
            $rows = explode("\n", $section);

            $first = array_shift($rows);
            $first = str_replace('<!-- ', '', $first);
            $first = str_replace(' -->', '', $first);
            $tmp = explode(' | ', $first);
            if (count($tmp) === 1) {
                $page = '_global';
            } else {
                $page = $tmp[1];
            }
            $code = trim(implode("\n", $rows));
            switch ($tmp[0]) {
                case 'HEAD START':
                    $this->headStart[$page] = $code;
                    break;
                case 'HEAD END':
                    $this->headEnd[$page] = $code;
                    break;
                case 'BODY START':
                    $this->bodyStart[$page] = $code;
                    break;
                case 'BODY END':
                    $this->bodyEnd[$page] = $code;
                    break;
            }
        }
    }
    public function getHeadStart(string $page, bool $fallback = true): ?string
    {
        return $this->headStart[$page] ?? ($fallback ? $this->headStart['_global'] ?? null : null);
    }
    public function getHeadEnd(string $page, bool $fallback = true): ?string
    {
        return $this->headEnd[$page] ?? ($fallback ? $this->headEnd['_global'] ?? null : null);
    }
    public function getBodyStart(string $page, bool $fallback = true): ?string
    {
        return $this->bodyStart[$page] ?? ($fallback ? $this->bodyStart['_global'] ?? null : null);
    }
    public function getBodyEnd(string $page, bool $fallback = true): ?string
    {
        return $this->bodyEnd[$page] ?? ($fallback ? $this->bodyEnd['_global'] ?? null : null);
    }
}
