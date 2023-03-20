<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleBackdropOpacity extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = floatval($args[0]) / 100.0;
        $this->setDeclaration('--tw-backdrop-opacity', 'opacity('.$value.')');
        $this->setDeclaration('backdrop-filter', 'var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)');
    }
}
