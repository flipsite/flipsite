<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleAccent extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        if ($this->setColor($args, 'accent-color')) {
            return;
        }
    }
}
