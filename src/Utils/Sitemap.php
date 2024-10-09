<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Flipsite\EnvironmentInterface;
use Flipsite\Data\SiteDataInterface;

final class Sitemap
{
    public function __construct(private EnvironmentInterface $environment, private SiteDataInterface $siteData)
    {
        $this->hidden = $siteData->getHiddenPages();
    }

    public function __toString(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
        $xml .= 'xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";
        $xml .= $this->urls();
        $xml .= '</urlset>'."\n";
        return $xml;
    }

    private function urls(): string
    {
        $xml = '';
        foreach ($this->siteData->getSlugs()->getAll() as $loc => $alternate) {
            if (in_array($loc, $this->hidden)) {
                continue;
            }
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->getUrl((string)$loc)."</loc>\n";
            $xml .= $this->getAlternate($alternate);
            $xml .= "  </url>\n";
        }
        return $xml;
    }

    private function getUrl(string $url): string
    {
        return $this->environment->getAbsoluteUrl($url);
    }

    /**
     * @param array<string,string> $alternate E.g. ['en' => 'flipsite.io',
     *                                        'fi' => 'flipsite.io/fi',]
     */
    private function getAlternate(array $alternate): string
    {
        if (count($alternate) <= 1) {
            return '';
        }
        $xml = '';
        foreach ($alternate as $language => $url) {
            $xml .= '    <xhtml:link rel="alternate" hreflang="'.$language.'" ';
            $xml .= 'href="'.$this->getUrl($url).'"/>'."\n";
        }
        return $xml;
    }
}
