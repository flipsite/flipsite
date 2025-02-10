<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Video extends AbstractComponent
{
    use Traits\AssetsTrait;

    protected string $tag = 'video';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data = $component->getData();
        if (isset($data['base64bg'])) {
            $this->setAttribute('style', 'background: url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        if (isset($data['poster'])) {
            $imageAttributes = $this->assets->getImageAttributes($data['poster'], $style['poster']['options'] ?? []);
            $this->setAttribute('poster', $imageAttributes->getSrc());
            unset($style['poster']);
        }
        if (isset($data['value']) && $videoAttributes = $this->assets->getVideoAttributes($data['value'])) {
            foreach ($videoAttributes->getSources() as $sourceAttributes) {
                $source = new Element('source', true);
                $source->setAttribute('src', $sourceAttributes->getSrc());
                $source->setAttribute('type', $sourceAttributes->getType());
                $source->setAttribute('media', $sourceAttributes->getMedia());
                $this->addChild($source);
            }
        }
    }
}
