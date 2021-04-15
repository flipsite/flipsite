<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Assets\ImageHandler;

trait ImageHandlerTrait
{
    protected ImageHandler $imageHandler;

    public function addImageHandler(ImageHandler $imageHandler) : void
    {
        $this->imageHandler = $imageHandler;
    }
}
