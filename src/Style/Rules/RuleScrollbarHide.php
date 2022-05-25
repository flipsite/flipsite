<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScrollbarHide extends AbstractRule
{
    use Traits\ColorTrait;

    protected ?string $pseudoElement = '::-webkit-scrollbar';

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $this->setDeclaration('display', 'none');
        return;
    }
}
