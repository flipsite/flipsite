<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

abstract class AbstractRuleSepia extends AbstractRule
{
    /**
     * @var array<string>
     */
    protected array $properties = [];

    protected bool $backdrop = false;
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration($this->properties[0], 'sepia('.UnitHelper::percentage($args[0]).')');

        if ($this->backdrop) {
            $this->setDeclaration('backdrop-filter', 'var(--tw-backdrop-blur) var(--tw-backdrop-brightness) var(--tw-backdrop-contrast) var(--tw-backdrop-grayscale) var(--tw-backdrop-hue-rotate) var(--tw-backdrop-invert) var(--tw-backdrop-opacity) var(--tw-backdrop-saturate) var(--tw-backdrop-sepia)');
        } else {
            $this->setDeclaration('filter', 'var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow)');
        }
    }
}
