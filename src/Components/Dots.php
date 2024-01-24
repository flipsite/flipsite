<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Builders\Event;

final class Dots extends AbstractGroup
{
    use Traits\BuilderTrait;
    protected string $tag  = 'ol';
    public function build(array $data, array $style, array $options): void
    {
        $this->setAttribute('role','list');
        $this->setAttribute('data-dots', true);
        $this->setAttribute('data-target', 'contacts');
        $this->addStyle($style);

        $style['dot']['tag'] = 'li';
        $dot = $this->builder->build('div', [
            '_noContent' => true,
            '_attr' => ['role' => 'listitem'],
        ], $style['dot'], $options);

        $this->builder->dispatch(new Event('ready-script', 'toggle', file_get_contents(__DIR__ . '/../../js/ready.dots.min.js')));

        $this->addChild($dot);
    }
}
