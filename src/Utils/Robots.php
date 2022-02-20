<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class Robots
{
    private bool $live;
    private string $baseUrl;

    public function __construct(bool $live, string $baseUrl = '')
    {
        $this->live    = $live;
        $this->baseUrl = $baseUrl;
    }

    public function __toString() : string
    {
        if ($this->live) {
            $rows = [
                'User-agent: *',
                'Allow: /',
                'Sitemap: '.$this->baseUrl.'/sitemap.xml',
            ];
        } else {
            $rows = [
                'User-agent: *',
                'Disallow: /',
                '# No sitemap if not live',
                '# Sitemap: '.$this->baseUrl.'/sitemap.xml',
            ];
        }
        return implode("\n", $rows);
    }
}
