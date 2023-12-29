<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleTranslate extends AbstractRule
{
    /*
     * @param array<string>
     */

    protected array $properties = [];

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->checkCallbacks('size', $args);
        if ($this->negative) {
            $this->negative = false;
            $value = '-'.$value;
        }
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translateX(var(--tw-translate-x)) translateY(var(--tw-translate-y)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
