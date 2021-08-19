<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Builders\SectionBuilder;

trait SectionBuilderTrait
{
    protected SectionBuilder $sectionBuilder;

    public function addSectionBuilder(SectionBuilder $sectionBuilder) : void
    {
        $this->sectionBuilder = $sectionBuilder;
    }
}
