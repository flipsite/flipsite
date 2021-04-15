<?php

declare(strict_types=1);

namespace Flipsite\Tests\Assets;

use Flipsite\Assets\ImageOptions;
use Flipsite\Assets\ImageSource;
use PHPUnit\Framework\TestCase;

class ImageSourceTest
{
    public function testCreate()
    {
        $options = new ImageOptions(['width' => 100]);
        $source  = new ImageSource('foo/bar.jpg', '123abc', $options);
        $this->assertSame('foo/bar@w100.123abc.jpg', $source->getSrc());
    }

    public function testSrc()
    {
        $options = new ImageOptions(['height' => 100]);
        $source  = new ImageSource('foo/bar.webp', '123abc', $options);
        $this->assertSame('foo/bar@h100.123abc.png', $source->getSrc());
        $sources = $source->getSources();
        $this->assertIsArray($sources);
        $this->assertEquals(2, count($sources));
    }

    public function testSrcsetEmpty()
    {
        $source = new ImageSource('foo/bar.webp', '123abc', new ImageOptions(['height' => 100]));
        $this->assertSame('foo/bar@h100.123abc.png', $source->getSrc());
        $sources = $source->getSources();
        $this->assertIsArray($sources);
        $this->assertEquals(2, count($sources));
        $this->assertEquals('image/webp', $sources[0]['type']);
        $this->assertEquals('image/png', $sources[1]['type']);
        $this->assertEquals('foo/bar@h100.123abc.webp', $sources[0]['srcset'][0]['src']);
        $this->assertEquals('foo/bar@h100.123abc.png', $sources[1]['srcset'][0]['src']);
    }

    public function testSrcsetX()
    {
        $source = new ImageSource('foo/bar.webp', '123abc', new ImageOptions(['width' => 100]), ['1x', '2x']);
        $this->assertSame('foo/bar@w100.123abc.png', $source->getSrc());
        $sources = $source->getSources();
        $this->assertIsArray($sources);
        $this->assertEquals(2, count($sources));
        $this->assertEquals('image/webp', $sources[0]['type']);
        $this->assertEquals('image/png', $sources[1]['type']);
        $this->assertEquals('foo/bar@w100.123abc.webp', $sources[0]['srcset'][0]['src']);
        $this->assertEquals('foo/bar@w200.123abc.webp', $sources[0]['srcset'][1]['src']);
        $this->assertEquals('foo/bar@w100.123abc.png', $sources[1]['srcset'][0]['src']);
        $this->assertEquals('foo/bar@w200.123abc.png', $sources[1]['srcset'][1]['src']);
    }
}
