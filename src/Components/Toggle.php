<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Toggle extends AbstractGroup
{
    use Traits\BuilderTrait;
    use Traits\PathTrait;

    protected string $tag = 'button';

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->setAttribute('aria-expanded','false');
        //$this->setAttribute('aria-controls', $data['value'].'-menu');
        $data['open'] = [
            'src' => $data['open'],
            '_attr' => [
                'aria-hidden' => 'true',
                'focusable' => 'false'
            ],
        ];
        $data['close'] = [
            'src' => $data['close'],
            '_attr' => [
                'aria-hidden' => 'true',
                'focusable' => 'false'
            ]
        ];
        $data['onclick'] = "javascript:toggle('".$data['value']."','open',this)";
        parent::build($data, $style, $appearance);
        $this->builder->dispatch(new Event('global-script', 'toggle', file_get_contents(__DIR__.'/../../js/toggle.min.js')));
    }
}
