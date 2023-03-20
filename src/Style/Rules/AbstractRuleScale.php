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
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
        $this->setDeclaration('transform', 'translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y));');
    }
}
