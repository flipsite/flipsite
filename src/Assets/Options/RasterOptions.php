<?php

declare(strict_types=1);
namespace Flipsite\Assets\Options;

final class RasterOptions extends AbstractImageOptions
{
    protected function defineOptions() : array
    {
        return [
            'alpha'       => new IntOption('a'),
            'blackWhite'  => new BoolOption('bw'),
            'blur'        => new IntOption('bl'),
            'height'      => new IntOption('h', true),
            'opacity'     => new IntOption('o'),
            'quality'     => new IntOption('q'),
            'width'       => new IntOption('w', true),
        ];
    }
}
