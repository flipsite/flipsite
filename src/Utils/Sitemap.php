<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Flipsite\Data\Slugs;

final class Sitemap
{
    private string $baseUrl;
    private Slugs $slugs;

    public function __construct(string $baseUrl, Slugs $slugs)
    {
        $this->baseUrl = $baseUrl;
        $this->slugs   = $slugs;
    }

    public function __toString() : string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";
        $xml .= $this->urls();
        $xml .= '</urlset>'."\n";
        return $xml;
    }

    private function urls() : string
    {
        $xml = '';
        foreach ($this->slugs->getAll() as $loc => $alternate) {
            if (str_ends_with((string)$loc,'404')) {
                continue;
            }
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->getUrl((string)$loc)."</loc>\n";
            $xml .= $this->getAlternate($alternate);
            $xml .= "  </url>\n";
        }
        return $xml;
    }

    private function getUrl(string $url) : string
    {
        return trim($this->baseUrl.'/'.$url, '/');
    }

    /**
     * @param array<string,string> $alternate E.g. ['en' => 'flipsite.io',
     *                                        'fi' => 'flipsite.io/fi',]
     */
    private function getAlternate(array $alternate) : string
    {
        if (count($alternate) <= 1) {
            return '';
        }
        $xml = '';
        foreach ($alternate as $language => $url) {
            if (404 === $url) {
                continue;
            }
            $xml .= '    <xhtml:link rel="alternate" hreflang="'.$language.'" ';
            $xml .= 'href="'.$this->getUrl($url).'"/>'."\n";
        }
        return $xml;
    }
}
