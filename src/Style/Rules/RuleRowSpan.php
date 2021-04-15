<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleRowSpan extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = intval($args[0]);
        $this->setDeclaration('grid-row', 'span '.$value.'/span '.$value);
    }
}
