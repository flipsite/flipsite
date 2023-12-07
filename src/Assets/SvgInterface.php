<?php

declare(strict_types=1);

namespace Flipsite\Assets;

interface SvgInterface
{
    public function getHash() : string;
    public function getViewbox() : string;
    public function getDef() : string;
}
