<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

abstract class AbstractRulePosition extends AbstractRule
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
        $value ??= $this->getConfig('spacing', $args[0]);
        foreach ($this->properties as $i => $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
