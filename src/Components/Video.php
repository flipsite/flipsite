<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Video extends AbstractComponent
{
    use Traits\AssetsTrait;

    protected string $tag = 'video';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['value'=>$data];
        }
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        if (isset($data['base64bg'])) {
            $this->setAttribute('style', 'background: url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        if (isset($data['poster'])) {
            $imageAttributes = $this->assets->getImageAttributes($data['poster'], $style['poster']['options'] ?? []);
            $this->setAttribute('poster', $imageAttributes->getSrc());
            unset($style['poster']);
        }
        $this->addStyle($style);
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
