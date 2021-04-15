<?php

declare(strict_types=1);

namespace Flipsite\Assets\Options;

final class SvgOptions extends AbstractImageOptions
{
    protected function defineOptions() : array
    {
        return [
            'fill' => new HexOption('f'),
        ];
    }
}
