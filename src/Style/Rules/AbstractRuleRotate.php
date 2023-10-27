<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleRotate extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('rotate', $args[0]);
        $value ??= UnitHelper::angle($args[0]);
        if ($this->negative) {
            $this->negative = false;
            $value = '-'.$value;
        }
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
