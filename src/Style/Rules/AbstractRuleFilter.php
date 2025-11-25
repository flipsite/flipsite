<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

abstract class AbstractRuleFilter extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    /**
     * @var array<string>
     */
    protected array $callbacks = [];

    protected string $unit     = '';
    protected string $function = '';
    protected bool $backdrop   = false;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        $value = null;

        // Handle different unit types
        switch ($this->unit) {
            case '%':
                $value = UnitHelper::percentage($args[0] ?? 100);
                break;
            case 'px':
                $value = intval($args[0] ?? 0) . 'px';
                break;
            case 'deg':
                $value = UnitHelper::angle($args[0] ?? 0);
                break;
        }

        // Handle callbacks if no value set yet
        if (null === $value) {
            foreach ($this->callbacks as $callback) {
                $value = $this->checkCallbacks($callback, $args);
                if (null !== $value) {
                    break;
                }
            }
        }

        // Handle negative values
        if ($this->negative) {
            $this->negative = false;
            $value          = '-' . $value;
        }

        // Apply the function wrapper if specified
        if ($this->function && null !== $value) {
            $value = $this->function . '(' . $value . ')';
        }

        // Set the CSS variable
        foreach ($this->properties as $property) {
            $this->setDeclaration($property, $value);
        }

        // Set the appropriate filter declaration
        if ($this->backdrop) {
            $this->setDeclaration('backdrop-filter', 'var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)');
        } else {
            $this->setDeclaration('filter', 'var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)');
        }
    }
}
