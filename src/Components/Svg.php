<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Svg extends AbstractComponent
{
    protected string $tag = 'svg';

    public function with(ComponentData $data) : void
    {
        if (count($data->getStyle('container'))) {
            $this->tag = 'div';
            $this->addStyle($data->getStyle('container'));
            $svg = new Element('svg', true);
            $svg->setAttribute('src', $data->get('value'));
            $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $svg->setAttribute('viewBox', '');
            $svg->setAttribute('width', '16');
            $svg->addStyle($data->getStyle());
            $svg->setContent('<use xlink:href=""></use>');
            $this->addChild($svg);
        } else {
            $this->oneline = true;
            $this->setAttribute('src', $data->get('value'));
            $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $this->setAttribute('viewBox', '');
            $this->setAttribute('width', '16');
            $this->addStyle($data->getStyle());
            $this->setContent('<use xlink:href=""></use>');
        }
    }
}
