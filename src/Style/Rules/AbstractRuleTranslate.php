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
        $value = $this->getConfig('translate', $args[0]);
        $value = $this->getConfig('spacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
