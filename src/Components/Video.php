<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Video extends AbstractComponent
{
    use Traits\VideoHandlerTrait;
    use Traits\ImageHandlerTrait;
    use Traits\CanIUseTrait;
    protected string $tag = 'video';

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['src' => $data];
        }
        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        if (isset($data['poster'])) {
            $src               = $data['poster'];
            $options           = $style['posterOptions'] ?? [];
            if ($this->canIUse->webp()) {
                $src = str_replace('.jpg', '.webp', $src);
                $src = str_replace('.png', '.webp', $src);
            }
            unset($style['posterOptions']);
            $imageContext = $this->imageHandler->getContext($src, $options);
            $this->setAttribute('poster', $imageContext->getSrc());
        }
        $this->addStyle($style);
        if (isset($data['src'])) {
            $sources = $this->videoHandler->getSources($data['src']);
            foreach ($sources as $type => $src) {
                $s = new Element('source', true);
                $s->setAttribute('src', $src);
                $s->setAttribute('type', $type);
                $this->addChild($s);
            }
        }
    }
}
