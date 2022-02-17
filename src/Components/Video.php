<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Video extends AbstractComponent
{
    use Traits\VideoHandlerTrait;
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
        $this->addStyle($style);
        $sources = $this->videoHandler->getSources($data['src']);
        foreach ($sources as $type => $src) {
            $s = new Element('source',true);
            $s->setAttribute('src', $src);
            $s->setAttribute('type', $type);
            $this->addChild($s);
        }
        //$this->content = $this->getMarkdownLine($data['value'], $style ?? null, $appearance);
    }
}
