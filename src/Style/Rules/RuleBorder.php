<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleBorder extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ($this->setColor($args, 'border-color', '--tw-border-opacity')) {
            return;
        }

        $variants = [
            't' => ['border-top-width'],
            'r' => ['border-right-width'],
            'b' => ['border-bottom-width'],
            'l' => ['border-left-width'],
        ];
        if (in_array($args[0] ?? null, array_keys($variants))) {
            $variant    = array_shift($args);
            $properties = $variants[$variant];
        } else {
            $properties = ['border-width'];
        }

        $value = $this->getConfig('borderWidth', $args[0] ?? 'DEFAULT');
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
