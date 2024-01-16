<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class RasterOptions extends AbstractImageOptions
{
    protected function defineOptions(): array
    {
        return [
            'alpha'       => new IntOption('a'),
            'blackWhite'  => new BoolOption('bw'),
            'blur'        => new IntOption('bl'),
            'height'      => new IntOption('h', true),
            'invert'      => new BoolOption('in'),
            'opacity'     => new IntOption('o'),
            'pixelate'    => new IntOption('px'),
            'position'    => new StringOption('p', [
                'b' => 'bottom',
                'c' => 'center',
                'l' => 'left',
                'lb' => 'left-bottom',
                'lt' => 'left-top',
                'r' => 'right',
                'rb' => 'right-bottom',
                'rt' => 'right-top',
                't' => 'top',
            ]),
            'quality'     => new IntOption('q'),
            'width'       => new IntOption('w', true),
        ];
    }
}
