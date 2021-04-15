<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleBg extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ('transparent' === $args[0]) {
            $this->setDeclaration('background-color', $args[0]);
            return;
        }
        if ($this->setColor($args, 'background-color', '--tw-bg-opacity')) {
            return;
        }

        $value = $this->getConfig('backgroundSize', $args[0]);
        if ($value) {
            $this->setDeclaration('background-size', $value);
            return;
        }

        $value = $this->getConfig('backgroundPosition', $args[0]);
        if ($value) {
            $this->setDeclaration('background-position', $value);
            return;
        }

        $value = $this->getConfig('backgroundImage', $args[0]);
        if ($value) {
            $this->setDeclaration('background-image', $value);
            return;
        }

        $value = $this->checkCallbacks('background-image', $args);
        if ($value) {
            $this->setDeclaration('background-image', $value);
            return;
        }
    }
}
