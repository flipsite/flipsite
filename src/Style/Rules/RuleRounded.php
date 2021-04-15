<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleRounded extends AbstractRule
{
    // /**
    //  * @var array<string>
    //  */
    // protected array $attributes = ['border-radius'];

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $variants = [
            't'  => ['border-top-left-radius', 'border-top-right-radius'],
            'r'  => ['border-top-right-radius', 'border-bottom-right-radius'],
            'b'  => ['border-bottom-right-radius', 'border-bottom-left-radius'],
            'l'  => ['border-top-left-radius', 'border-bottom-left-radius'],
            'tl' => ['border-top-left-radius'],
            'tr' => ['border-top-right-radius'],
            'br' => ['border-bottom-right-radius'],
            'bl' => ['border-bottom-left-radius'],
        ];
        if (in_array($args[0] ?? null, array_keys($variants))) {
            $variant    = array_shift($args);
            $properties = $variants[$variant];
        } else {
            $properties = ['border-radius'];
        }

        $value = $this->getConfig('borderRadius', $args[0] ?? 'DEFAULT');
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
}
