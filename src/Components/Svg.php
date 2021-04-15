<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Svg extends AbstractComponent
{
    protected string $type = 'svg';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->oneline = true;
        $this->setAttribute('src', $data['value']);
        $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        $this->setAttribute('viewBox', '');
        $this->setAttribute('width', '16');
        $this->addStyle($style);
        $this->setContent('<use xlink:href=""></use>');
    }
}
