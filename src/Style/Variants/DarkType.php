<?php

declare(strict_types=1);

namespace Flipsite\Style\Variants;

class DarkType extends AbstractType
{
    protected string $prefix = 'dark';
    protected int $order;

    public function __construct(string $darkMode)
    {
        if ('media' === $darkMode) {
            $this->order      = 300;
            $this->mediaQuery = '(prefers-color-scheme:dark)';
        } else {
            $this->order  = 50;
            $this->parent = 'dark';
        }
    }
}
