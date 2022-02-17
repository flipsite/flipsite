<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Assets\VideoHandler;

trait VideoHandlerTrait
{
    protected VideoHandler $VideoHandler;

    public function addVideoHandler(VideoHandler $videoHandler) : void
    {
        $this->videoHandler = $videoHandler;
    }
}
