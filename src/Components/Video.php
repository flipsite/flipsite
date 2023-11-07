<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Video extends AbstractComponent
{
    use Traits\VideoHandlerTrait;
    use Traits\BuilderTrait;
    
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
            $this->setAttribute('style','background: url('.$data['base64bg'].') 0% 0% / cover no-repeat;');
        }
        if (isset($data['poster'])) {
            $img = $this->builder->build('image', ['src' => $data['poster']], $style['poster'] ?? [], ['appearance' => $options['appearance']]);
            $this->setAttribute('poster', $img->getAttribute('src'));
            unset($style['poster']);
        }
        $this->addStyle($style);
        if (isset($data['value'])) {
            $sources = $this->videoHandler->getSources($data['value']);
            foreach ($sources as $type => $src) {
                $s = new Element('source', true);
                $s->setAttribute('src', $src);
                $s->setAttribute('type', $type);
                $this->addChild($s);
            }
        }
    }
}
