<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Data\SiteDataInterface;

trait SiteDataTrait
{
    protected SiteDataInterface $siteData;

    public function addSiteData(SiteDataInterface $siteData) : void
    {
        $this->siteData = $siteData;
    }
}
