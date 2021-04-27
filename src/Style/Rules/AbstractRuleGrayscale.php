<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

abstract class AbstractRuleGrayscale extends AbstractRule
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
        $this->setDeclaration($this->properties[0], 'grayscale('.UnitHelper::percentage($args[0]).')');
    }
}
