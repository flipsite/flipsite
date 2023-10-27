<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

class RulePerspective extends AbstractRule
{
    
/**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        $this->setDeclaration('--tw-perspective', $value);
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');

    }
}
