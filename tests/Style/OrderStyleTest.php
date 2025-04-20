<?php

use PHPUnit\Framework\TestCase;
use Flipsite\Style\OrderStyle;

class OrderStyleTest extends TestCase
{
    public function testOddEven()
    {
        $setting = new OrderStyle('flex-col md:even:flex-row md:odd:flex-row-reverse');

        $even = 'flex-col md:flex-row';
        $odd = 'flex-col md:flex-row-reverse';

        $this->assertSame($setting->getValue(1, 12), $odd);
        $this->assertSame($setting->getValue(5, 12), $odd);
        $this->assertSame($setting->getValue(2, 12), $even);
        $this->assertSame($setting->getValue(12, 12), $even);
    }
    public function testFirst()
    {
        $setting = new OrderStyle('flex-col md:first:flex-row');
        $this->assertSame($setting->getValue(1, 12), 'flex-col md:flex-row');
        $this->assertSame($setting->getValue(2, 12), 'flex-col');
        $this->assertSame($setting->getValue(12, 12), 'flex-col');
    }

    public function testLast()
    {
        $setting = new OrderStyle('flex-col md:last:flex-row');
        $this->assertSame($setting->getValue(1, 12), 'flex-col');
        $this->assertSame($setting->getValue(2, 12), 'flex-col');
        $this->assertSame($setting->getValue(13, 13), 'flex-col md:flex-row');
    }
}
