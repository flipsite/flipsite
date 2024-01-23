<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScrollbars extends AbstractRule
{
    use Traits\ColorTrait;

    protected ?string $pseudoElement = '::-webkit-scrollbar';

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        if (isset($args[0]) && $args[0] === 'hidden') {
            $this->setDeclaration('display', 'none');
        }
        return;
    }
}
