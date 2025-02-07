<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;
use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

class Iframe extends AbstractComponent
{
    protected bool $oneline = true;
    protected string $tag   = 'iframe';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data  = $component->getData();
        $width = intval($data['width'] ?? 0);
        if ($width) {
            $this->setAttribute('width', $width);
        }
        $height = intval($data['height'] ?? 0);
        if ($height) {
            $this->setAttribute('height', $height);
        }
        if (isset($data['sandbox'])) {
            $list = ArrayHelper::decodeJsonOrCsv($data['sandbox']);
            $this->setAttribute('sandbox', implode(' ', $list));
        }
    }
}
