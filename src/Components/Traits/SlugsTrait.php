<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Data\Slugs;

trait SlugsTrait
{
    protected Slugs $slugs;

    public function addSlugs(Slugs $slugs) : void
    {
        $this->slugs = $slugs;
    }
}
