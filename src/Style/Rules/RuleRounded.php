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
        } else if (in_array($args[0], ['frame','btn'])) {
            $theme = 'xl';
            $value = $this->getThemeValue($theme, $args[0], $args[1]);

        } else if ($args[0] === 'full') {
            $value = '9999px';
        }
        $value ??= $this->checkCallbacks('size', $args);
        foreach ($properties as $property) {
            $this->setDeclaration($property, $value);
        }
    }
    private function getThemeValue(string $theme, string $type, string $size) {
        if ('none' === $theme) {
            return null;
        }
        if ('full' === $theme && 'btn' === $type) {
            return '9999px';
        }
        
        $radius = [
            'xs' => 0.25,
            'sm' => 0.5,
            'md' => 0.75,
            'lg' => 1,
            'xl' => 1.5,
        ];
        $radius = $radius[$size] ?? null;
        if (null === $radius) {
            return;
        }

        $multiplier = [
            'xs' => 0.8,
            'sm' => 1.0,
            'md' => 1.2,
            'lg' => 1.4,
            'xl' => 1.6,
        ];
        echo $radius*= $multiplier[$theme] ?? 1.0;
        return $radius.'rem';
    }
}
