<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleScale extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('scale', $args[0]);
        $value ??= floatval($args[0]) / 100.0;
        if ($this->negative) {
            $this->negative = false;
            $value*=-1.0;
        }
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
        $this->setDeclaration('transform', 'perspective(var(--tw-perspective)) translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) rotateX(var(--tw-rotate-x)) rotateY(var(--tw-rotate-y)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
