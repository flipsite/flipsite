<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Toggle extends AbstractComponent
{
    use Traits\BuilderTrait;
    protected string $type = 'div';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        $this->setAttribute('onclick', "toggle(this);toggle(document.getElementById('".$data['target']."'))");
        $default = $this->builder->build('svg', $data['default'], $style);
        $default->addStyle($style['default'] ?? []);
        $this->addChild($default);

        $active = $this->builder->build('svg', $data['active'], $style);
        $active->addStyle($style['active'] ?? []);
        $this->addChild($active);

        //$component = $this->builder->build($type, $val, $style[$key] ?? []);

        // $icon = $this->builder->build('svg', $data['icon'], $style['icon']);
        // $this->addChild($icon);

        // $iframe = new Element('iframe', true);
        // $iframe->addStyle($style['iframe'] ?? []);
        // $iframe->setAttributes([
        //     'loading'         => 'lazy',
        //     'frameborder'     => '0',
        //     'allow'           => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
        //     'allowfullscreen' => true,
        //     'data-src'        => '//www.youtube.com/embed/'.$data['id'].'?autoplay=1&mute=1',
        //     'title'           => $data['title'],
        // ]);
        // $this->addChild($iframe);

        $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/toggle.js')));
        // $this->oneline = true;
        // $this->setAttribute('src', $data['value']);
        // $this->setAttribute('xmlns', 'http://www.w3.org/2000/svg');
        // $this->setAttribute('viewBox', '');
        // $this->setAttribute('width', '16');
        // $this->addStyle($style);
    }
}
