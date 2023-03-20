<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleRotate extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('rotate', $args[0]);
        $value ??= UnitHelper::angle($args[0]);
        $this->setDeclaration('--tw-rotate', $value);
        $this->setDeclaration('transform', 'translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
