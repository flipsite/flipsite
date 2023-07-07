<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Svg extends AbstractComponent
{
    protected string $tag   = 'svg';
    protected bool $oneline = true;

    public function normalize(string|int|bool|array $data) : array
    {
        if (!is_array($data)) {
            return ['src' => $data];
        } elseif (isset($data['value'])) {
            $data['src'] = $data['value'];
            unset($data['value']);
        }
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        if (isset($style['wrapper'])) {
            $this->addStyle($style['wrapper']);
            unset($style['wrapper']);
            $this->tag = 'div';
            $svg       = new Element('svg', true);
            $svg->setAttribute('src', $data['src']);
            $svg->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $svg->setAttribute('viewBox', '');
            $svg->addStyle($style);
            $svg->setContent('<use xlink:href=""></use>');
            $this->addChild($svg);
        } else {
            $this->setAttribute('src', $data['src']);
            $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            $this->setAttribute('viewBox', '');
            $this->addStyle($style);
            $this->setContent('<use xlink:href=""></use>');
        }
    }
}
