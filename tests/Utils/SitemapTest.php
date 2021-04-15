<?php

declare(strict_types=1);

namespace Flipsite\Tests\Utils;

use Flipsite\Data\Slugs;
use Flipsite\Utils\Language;
use Flipsite\Utils\Sitemap;
use PHPUnit\Framework\TestCase;

class SitemapTest extends TestCase
{
    public function testOneLanguage()
    {
        $slugs    = new Slugs(['home', 'features', 'pricing'], null, new Language('en'));
        $sitemap  = new Sitemap('https://flipsite.io', $slugs);
        $expected = file_get_contents(__DIR__.'/sitemap.xml');
        $this->assertSame($expected, (string) $sitemap);
    }

    public function testMultilingual()
    {
        $slugs    = new Slugs(['home', 'features', 'pricing'], ['features' => ['fi' => 'toiminnot']], new Language('en'), [new Language('en'), new Language('fi')]);
        $sitemap  = new Sitemap('https://flipsite.io', $slugs);
        $expected = file_get_contents(__DIR__.'/sitemap-multilingual.xml');
        $this->assertSame($expected, (string) $sitemap);
    }
}
