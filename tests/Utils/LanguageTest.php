<?php

declare(strict_types=1);

namespace Flipsite\Tests\Utils;

use Flipsite\Utils\Language;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function testCreate()
    {
        $en = new Language('en');
        $this->assertSame('en', (string) $en);
        $this->expectException('TypeError');
        $en = new Language('ax');
    }

    public function testIs()
    {
        $en = new Language('en');
        $fi = new Language('fi');
        $this->assertTrue($en->isSame($en));
        $this->assertFalse($fi->isSame($en));
    }

    public function testToUpper()
    {
        $en = new Language('en');
        $de = new Language('de');
        $this->assertSame('EN', $en->upper());
        $this->assertSame('DE', $de->upper());
    }

    public function testToString()
    {
        $this->assertSame('co', (string) (new Language('co')));
        $this->assertSame('cr', (string) (new Language('cr')));
        $this->assertSame('fi', (string) (new Language('fi')));
        $this->assertSame('sv', (string) (new Language('sv')));
    }
}
