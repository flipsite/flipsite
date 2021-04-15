<?php

declare(strict_types=1);

namespace Flipsite\Tests\Assets;

use Flipsite\Assets\ImageOptions;
use PHPUnit\Framework\TestCase;

class ImageOptionsTest
{
    public function testCreate()
    {
        $options = new ImageOptions([
            'width'      => 100,
            'height'     => 50,
            'alpha'      => 30,
            'blackWhite' => true,
            'blur'       => 5,
            'trim'       => true,
        ]);
        $this->assertEquals(100, $options->getWidth());
        $this->assertEquals(50, $options->getHeight());
        $this->assertEquals('@w100,h50,a30,bw,bl5,t', (string) $options);
    }

    public function testCreateNull()
    {
        $options = new ImageOptions(null);
        $this->assertNull($options->getWidth());
        $this->assertNull($options->getHeight());
        $this->assertEquals('', (string) $options);
    }

    public function testCreateNoOptions()
    {
        $options = new ImageOptions();
        $this->assertNull($options->getWidth());
        $this->assertNull($options->getHeight());
        $this->assertEquals('', (string) $options);
    }

    public function testFromRequest()
    {
        $options = ImageOptions::fromRequest('test/foo@w100,h50.abc123.png');

        $this->assertEquals(100, $options->getWidth());
        $this->assertEquals(50, $options->getHeight());
        $this->assertEquals('@w100,h50', (string) $options);

        $options = ImageOptions::fromRequest('foo.jpg');
        $this->assertEquals('', (string) $options);

        $options = ImageOptions::fromRequest('foo.abc123.jpg');
        $this->assertEquals('', (string) $options);

        $options = ImageOptions::fromRequest('fooabc123@t.svg');
        $this->assertEquals('@t', (string) $options);
    }

    public function testSetFromRequest()
    {
        $options = ImageOptions::fromRequest('test/foo@w100,h50.abc123.png');
        $this->assertEquals(100, $options->getWidth());
        $this->assertEquals(50, $options->getHeight());
    }

    public function testScale()
    {
        $options = new ImageOptions([
            'width'  => 100,
            'height' => 150,
            'scale'  => 1.5,
        ]);
        $this->assertEquals(150, $options->getWidth());
        $this->assertEquals(225, $options->getHeight());
    }
}
