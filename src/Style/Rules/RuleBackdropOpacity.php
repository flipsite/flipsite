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
    }
}
