<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

class Svg extends Image
{
    use Traits\BuilderTrait;
    use Traits\AssetsTrait;

    protected string $tag   = 'svg';
    protected bool $oneline = true;

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $component->setDataValue('inlineSvg', true);
        parent::build($component, $inherited);
    }
}
