<?php

declare(strict_types=1);

namespace Flipsite\Tests\Utils;

use Flipsite\Utils\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
    public function testIsAssoc()
    {
        $this->assertTrue(ArrayHelper::isAssociative(['foo' => 'bar']));
        $this->assertFalse(ArrayHelper::isAssociative([0 => 'foo', 1 => 'bar']));
    }

    public function unDot()
    {
        $input = ['aaa' => 'Foo'];
        $this->assertSame($input, ArrayHelper::unDot($input));

        $input  = ['aaa.bbb' => 'Foo'];
        $output = ['aaa' => ['bbb' => 'Foo']];
        $this->assertSame($output, ArrayHelper::unDot($input));

        $input = [
            'aaa.bbb.ccc' => 'Foo',
            'aaa'         => [
                'ddd' => [
                    'eee' => 'Bar',
                ],
            ],
        ];
        $output = [
            'aaa' => [
                'bbb' => [
                    'ccc' => 'Foo',
                ],
                'ddd' => [
                    'eee' => 'Bar',
                ],
            ],
        ];

        $this->assertSame($output, ArrayHelper::unDot($input));

        $input = [
            'aaa' => [
                [
                    'bbb.ccc' => 'Foo',
                ],
            ],
        ];
        $output = [
            'aaa' => [
                [
                    'bbb' => [
                        'ccc' => 'Foo',
                    ],
                ],
            ],
        ];

        $this->assertSame($output, ArrayHelper::unDot($input));

        $input = [
            'aaa.aaa' => 'Foo',
            'aaa.bbb' => 'Bar',
        ];
        $output = [
            'aaa' => [
                'aaa' => 'Foo',
                'bbb' => 'Bar',
            ],
        ];

        $this->assertSame($output, ArrayHelper::unDot($input));
    }
}
