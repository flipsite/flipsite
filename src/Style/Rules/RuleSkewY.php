<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleSkewY extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('skew', $args[0]);
        $value ??= $args[0].'deg';
        if ($this->negative) {
            $this->negative = false;
            $value = '-'.$value;
        }
        $this->setDeclaration('--tw-skew-y', $value);
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translateX(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
