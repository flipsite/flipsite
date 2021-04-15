<?php

declare(strict_types=1);

namespace Flipsite\Tests;

use Flipsite\Data\Slugs;
use Flipsite\Utils\Language;
use Flipsite\Utils\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testHome()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing'], [
            'pricing' => ['fi' => 'hinnat', 'sv' => 'priser'],
        ], $en, [$en, $fi, $sv]);

        $path = new Path('', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('home', $path->getPage());
        $this->assertSame('en', (string) $path->getLanguage());

        $path = new Path('fi', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('home', $path->getPage());
        $this->assertSame('fi', (string) $path->getLanguage());
    }

    public function testSlugs()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing', 'features'], [
            'pricing' => ['fi' => 'hinnat', 'sv' => 'priser'],
        ], $en, [$en, $fi, $sv]);

        $path = new Path('priser', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('pricing', $path->getPage());
        $this->assertSame('sv', (string) $path->getLanguage());

        $path = new Path('sv/features', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('features', $path->getPage());
        $this->assertSame('sv', (string) $path->getLanguage());
    }

    public function testRedirects()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing', 'contact/people'], [
            'pricing'        => ['fi' => 'hinnat', 'sv' => 'priser'],
            'contact/people' => ['fi' => 'yhteys/henkilöt', 'sv' => 'kontakt/personer'],
        ], $en, [$en, $fi, $sv]);

        $path = new Path('en', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('home', $path->getPage());
        $this->assertSame('en', (string) $path->getLanguage());
        $this->assertSame('', (string) $path->getRedirect());

        $path = new Path('fi/hinnat', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('pricing', $path->getPage());
        $this->assertSame('fi', (string) $path->getLanguage());
        $this->assertSame('hinnat', (string) $path->getRedirect());

        $path = new Path('fi/pricing', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('pricing', $path->getPage());
        $this->assertSame('fi', (string) $path->getLanguage());
        $this->assertSame('hinnat', $path->getRedirect());

        $path = new Path('en/hinnat', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('pricing', $path->getPage());
        $this->assertSame('en', (string) $path->getLanguage());
        $this->assertSame('pricing', $path->getRedirect());

        $path = new Path('fi/yhteys/henkilöt', $en, [$en, $fi, $sv], $slugs);
        $this->assertSame('contact/people', $path->getPage());
        $this->assertSame('fi', (string) $path->getLanguage());
        $this->assertSame('yhteys/henkilöt', $path->getRedirect());
    }

    public function testSimilairRedirects()
    {
        $sv    = new Language('sv');
        $slugs = new Slugs(['home', 'projekt'], null, $sv);
        $path  = new Path('prjekt', $sv, [$sv], $slugs);

        $this->assertSame('projekt', $path->getPage());
        $this->assertSame('sv', (string) $path->getLanguage());
        $this->assertSame('projekt', $path->getRedirect());
    }
}
