<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTo extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $color = $this->getColor($args);
        if ($color) {
            $this->setDeclaration('--tw-gradient-to', (string) $color.'!important');
        }
    }
}
