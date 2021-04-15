<?php

declare(strict_types=1);

namespace Flipsite\Tests\Assets;

use Flipsite\Assets\Options\RasterOptions;
use PHPUnit\Framework\TestCase;

class RasterOptionsTest extends TestCase
{
    public function testCreate()
    {
        $options = new RasterOptions();
        $this->assertEquals('', (string) $options);

        $options = new RasterOptions(['width' => 100, 'blackWhite' => true]);
        $this->assertEquals('@bw,w100', (string) $options);
        $this->assertEquals(100, $options->getValue('width'));
        $this->assertTrue($options->getValue('blackWhite'));
    }

    public function testCreateFromPath()
    {
        $options = new RasterOptions('test/foo.png');
        $this->assertEquals('', (string) $options);

        $options = new RasterOptions('test/foo@w100,h200,a50.png');
        $this->assertEquals('@a50,h200,w100', (string) $options);
        $this->assertEquals(100, $options->getValue('width'));
        $this->assertEquals(200, $options->getValue('height'));
        $this->assertEquals(50, $options->getValue('alpha'));
    }

    public function testScale()
    {
        $options = new RasterOptions('test/foo@w100,h200,a50.png');
        $this->assertEquals('@a50,h200,w100', (string) $options);
        $options->changeScale(2.0);
        $this->assertEquals('@a50,h400,w200', (string) $options);
        $this->assertEquals(100, $options->getValue('width'));
        $this->assertEquals(200, $options->getValue('height'));
        $this->assertEquals(50, $options->getValue('alpha'));
    }
}
