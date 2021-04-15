<?php

declare(strict_types=1);

namespace Flipsite\Tests\Utils;

use Flipsite\Utils\Robots;
use PHPUnit\Framework\TestCase;

class RobotsTest extends TestCase
{
    public function testDev()
    {
        $expected = file_get_contents(__DIR__.'/robots-dev.txt');
        $robots   = new Robots(false);
        $this->assertSame($expected, (string) $robots);
    }

    public function testLive()
    {
        $expected = file_get_contents(__DIR__.'/robots-live.txt');
        $robots   = new Robots(true, 'https://flipsite.io');
        $this->assertSame($expected, (string) $robots);
    }
}
