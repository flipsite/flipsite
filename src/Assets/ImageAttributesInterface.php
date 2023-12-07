<?php

declare(strict_types=1);

namespace Flipsite\Assets;

interface ImageAttributesInterface
{
    public function getSrc() : string;
    public function getSrcset(?string $type = null) : ?string;
    public function getWidth() : ?int;
    public function getHeight() : ?int;
}
