<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleInset extends AbstractRuleSpacing
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $properties = ['left', 'right', 'top', 'bottom'];
        if ('x' === $args[0]) {
            array_shift($args);
            $properties = ['left', 'right'];
        } elseif ('y' === $args[0]) {
            array_shift($args);
            $properties = ['top', 'bottom'];
        }
        $value = $this->getConfig('inset', $args[0]);
        $value ??= $this->getConfig('spacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
