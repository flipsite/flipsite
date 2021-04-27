<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

abstract class AbstractRuleHueRotate extends AbstractRule
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
        $value = UnitHelper::angle($args[0]);
        if ($this->negative) {
            $value          = '-'.$value;
            $this->negative = false;
        }
        $this->setDeclaration($this->properties[0], 'hue-rotate('.$value.')');
    }
}
