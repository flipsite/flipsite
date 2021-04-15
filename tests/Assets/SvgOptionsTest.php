<?php

declare(strict_types=1);

namespace Flipsite\Tests\Assets;

use Flipsite\Assets\Options\SvgOptions;
use PHPUnit\Framework\TestCase;

class SvgOptionsTest extends TestCase
{
    public function testCreate()
    {
        $options = new SvgOptions();
        $this->assertEquals('', (string) $options);

        $options = new SvgOptions(['fill' => '#001122']);
        $this->assertEquals('@f001122', (string) $options);
        $this->assertEquals('#001122', $options->getValue('fill'));
    }

    public function testCreateFromPath()
    {
        $options = new SvgOptions('test/foo.svg');
        $this->assertEquals('', (string) $options);

        $options = new SvgOptions('test/foo@f001122.svg');
        $this->assertEquals('@f001122', (string) $options);
        $this->assertEquals('#001122', $options->getValue('fill'));
    }

    public function testScale()
    {
        $options = new SvgOptions('test/foo.svg');
        $this->assertEquals('', (string) $options);

        $options = new SvgOptions('test/foo@f001122.svg');
        $this->assertEquals('@f001122', (string) $options);
        $this->assertEquals('#001122', $options->getValue('fill', 2.0));
    }
}
