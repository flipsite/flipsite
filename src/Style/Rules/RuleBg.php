<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleBg extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        $this->setColor($args, 'background-color');
    }
}
