<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleOutline extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ($this->setColor($args, 'outline-color')) {
            return;
        }

        $this->setDeclaration('outline-offset', intval($args[0].'px'));
    }
}
