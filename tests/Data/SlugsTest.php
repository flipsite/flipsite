<?php

declare(strict_types=1);

namespace Flipsite\Tests\Data;

use Flipsite\Data\Slugs;
use Flipsite\Utils\Language;
use PHPUnit\Framework\TestCase;

class SlugsTest extends TestCase
{
    public function testOneLanguage()
    {
        $slugs = new Slugs(['home', 'pricing', 'contact'], null, new Language('en'));

        $this->assertNull($slugs->getPath('missing'));
        $this->assertSame('/', $slugs->getPath('home'));
        $this->assertSame('/', $slugs->getPath('/home'));
        $this->assertSame('/', $slugs->getPath('/'));
        $this->assertSame('/', $slugs->getPath('/en'));
        $this->assertSame('/pricing', $slugs->getPath('pricing'));
        $this->assertSame('/contact', $slugs->getPath('contact'));
    }

    public function testTwoLanguages()
    {
        $en    = new Language('en');
        $fi    = new Language('fi');
        $slugs = new Slugs(['home', 'pricing', 'contact'], [
            'pricing' => 'hinnat',
        ], $en, [$en, $fi]);

        $this->assertSame('/', $slugs->getPath('home', $en));
        $this->assertSame('/fi', $slugs->getPath('home', $fi));
        $this->assertSame('/pricing', $slugs->getPath('pricing', $en));
        $this->assertSame('/hinnat', $slugs->getPath('pricing', $fi));
        $this->assertSame('/fi/contact', $slugs->getPath('contact', $fi));
        $this->assertSame('/pricing', $slugs->getPath('hinnat', $en));
    }

    public function testMoreThanTwoLanguages()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing', 'contact'], [
            'pricing' => ['fi' => 'hinnat', 'sv' => 'priser'],
        ], $en, [$en, $fi, $sv]);

        $this->assertSame('/', $slugs->getPath('home', $en));
        $this->assertSame('/fi', $slugs->getPath('home', $fi));
        $this->assertSame('/sv', $slugs->getPath('home', $sv));
        $this->assertSame('/pricing', $slugs->getPath('pricing', $en));
        $this->assertSame('/hinnat', $slugs->getPath('pricing', $fi));
        $this->assertSame('/priser', $slugs->getPath('pricing', $sv));
        $this->assertSame('/fi/contact', $slugs->getPath('contact', $fi));
        $this->assertSame('/sv/contact', $slugs->getPath('contact', $sv));
        $this->assertSame('/pricing', $slugs->getPath('hinnat', $en));
        $this->assertSame('/pricing', $slugs->getPath('priser', $en));
        $this->assertSame('/contact', $slugs->getPath('fi/contact', $en));
        $this->assertSame('/contact', $slugs->getPath('sv/contact', $en));
    }

    public function testChangeLanguage()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home'], [], $en, [$en, $fi, $sv]);

        $this->assertSame('/', $slugs->getPath('en', $en));
        $this->assertSame('/fi', $slugs->getPath('fi', $en));
        $this->assertSame('/sv', $slugs->getPath('sv', $en));

        $this->assertSame('/', $slugs->getPath('en', $fi));
        $this->assertSame('/fi', $slugs->getPath('fi', $fi));
        $this->assertSame('/sv', $slugs->getPath('sv', $fi));
    }

    public function testSitemapDataOneLanguage()
    {
        $en       = new Language('en');
        $slugs    = new Slugs(['home', 'pricing', 'contact'], null, $en);
        $expected = [
            ''        => ['en' => ''],
            'pricing' => ['en' => 'pricing'],
            'contact' => ['en' => 'contact'],
        ];
        $this->assertSame($slugs->getAll(), $expected);
    }

    public function testSitemapDataMultilingual()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing', 'contact', 'contact/people'], [
            'pricing'        => ['fi' => 'hinnat', 'sv' => 'priser'],
            'contact/people' => ['fi' => 'yhteys/henkilöt'],
        ], $en, [$en, $fi, $sv]);

        $expected = [
            '' => [
                'en' => '',
                'fi' => 'fi',
                'sv' => 'sv',
            ],
            'pricing' => [
                'en' => 'pricing',
                'fi' => 'hinnat',
                'sv' => 'priser',
            ],
            'contact' => [
                'en' => 'contact',
                'fi' => 'fi/contact',
                'sv' => 'sv/contact',
            ],
            'contact/people' => [
                'en' => 'contact/people',
                'fi' => 'yhteys/henkilöt',
                'sv' => 'sv/contact/people',
            ],
            'fi' => [
                'en' => '',
                'fi' => 'fi',
                'sv' => 'sv',
            ],
            'hinnat' => [
                'en' => 'pricing',
                'fi' => 'hinnat',
                'sv' => 'priser',
            ],
            'fi/contact' => [
                'en' => 'contact',
                'fi' => 'fi/contact',
                'sv' => 'sv/contact',
            ],
            'yhteys/henkilöt' => [
                'en' => 'contact/people',
                'fi' => 'yhteys/henkilöt',
                'sv' => 'sv/contact/people',
            ],
            'sv' => [
                'en' => '',
                'fi' => 'fi',
                'sv' => 'sv', ],
            'priser' => [
                'en' => 'pricing',
                'fi' => 'hinnat',
                'sv' => 'priser',
            ],
            'sv/contact' => [
                'en' => 'contact',
                'fi' => 'fi/contact',
                'sv' => 'sv/contact',
            ],
            'sv/contact/people' => [
                'en' => 'contact/people',
                'fi' => 'yhteys/henkilöt',
                'sv' => 'sv/contact/people',
            ],
        ];
        $this->assertSame($slugs->getAll(), $expected);
    }

    public function testSimilarPage()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $sv = new Language('sv');

        $slugs = new Slugs(['home', 'pricing', 'contact', 'contact/people'], [
            'pricing'        => ['fi' => 'hinnat', 'sv' => 'priser'],
            'contact/people' => ['fi' => 'yhteys/henkilöt'],
        ], $en, [$en, $fi, $sv]);

        $this->assertSame('pricing', $slugs->getSimilarPage('picing'));
    }
}
