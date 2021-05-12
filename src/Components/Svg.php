<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Svg extends AbstractComponent
{
    protected string $type = 'svg';

    public function build(array $data, array $style, array $flags) : void
    {
        if (isset($style['container'])) {
            $this->type = 'div';
            $this->addStyle($style['container']);
            $svg = new Element('svg', true);
            $svg->setAttribute('src', $data['value']);
            $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $svg->setAttribute('viewBox', '');
            $svg->setAttribute('width', '16');
            $svg->addStyle($style);
            $svg->setContent('<use xlink:href=""></use>');
            $this->addChild($svg);
        } else {
            $this->oneline = true;
            $this->setAttribute('src', $data['value']);
            $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $this->setAttribute('viewBox', '');
            $this->setAttribute('width', '16');
            $this->addStyle($style);
            $this->setContent('<use xlink:href=""></use>');
        }
    }
}
