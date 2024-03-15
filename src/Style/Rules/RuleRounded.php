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
        $value = null;
        if (!isset($args[0])) {
            $args[0] = '1';
        } else if (in_array($args[0], ['box','btn'])) {
            $scale = 1.5;
            if ($scale < 0.5) {
                return;
            }
            if ('btn' === $args[0] && $scale > 1.99) {
                $value = '9999px';
            } else {
                $value = $this->getScaledValue($scale, $args[1]);
            }
        }
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
    private function getScaledValue(float $scale, string $size) {
        $radiusPx = [
            'xs' => 2.25,
            'sm' => 3,
            'md' => 4.5,
            'lg' => 6,
            'xl' => 9,
        ];
        return  ($radiusPx[$size] * $scale / 16.0) . 'rem';        
    }
}
