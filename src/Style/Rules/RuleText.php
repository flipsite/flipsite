<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleText extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        if ('transparent' === $args[0]) {
            $this->setDeclaration('color', 'transparent');
            return;
        }
        if ($this->setColor($args, 'color')) {
            return;
        }
        // $args[] = rand(75,125)/100.0;
        // $args[] = '_multiplier';
        $value = $this->checkCallbacks('size', $args);
        if ($value) {
            $this->setDeclaration('font-size', $value);
        }
    }
}
