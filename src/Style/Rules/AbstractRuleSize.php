<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleSize extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        $value = $this->checkCallbacks('size', $args);
        foreach ($this->properties as $i => $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
