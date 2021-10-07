<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleRing extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ($this->setColor($args, '--tw-ring-color', '--tw-ring-opacity')) {
            return;
        }

        $width = isset($args[0]) ? intval($args[0]) : 3;
        $this->setDeclaration('box-shadow', ' 0 0 0 calc('.$width.'px) var(--tw-ring-color)');
    }
}
