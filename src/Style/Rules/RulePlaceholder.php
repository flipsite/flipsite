<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RulePlaceholder extends AbstractRule
{
    use Traits\ColorTrait;

    protected ?string $pseudoElement = '::placeholder';

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if ('transparent' === $args[0]) {
            $this->setDeclaration('color', 'transparent');
            return;
        }
        if ($this->setColor($args, 'color', '--tw-placeholder-opacity')) {
            return;
        }
    }
}
