<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleDecoration extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args): void
    {
        if ($this->setColor($args, 'text-decoration-color')) {
            return;
        }
        
        $value = intval($args[0]).'px';
        $this->setDeclaration('text-decoration-thickness', $value);
    }
}
