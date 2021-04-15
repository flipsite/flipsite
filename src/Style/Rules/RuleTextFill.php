<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTextFill extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $color = 'transparent' === $args[0] ? 'transparent' : $this->getColor($args);
        $this->setDeclaration('-webkit-text-fill-color', (string) $color);
    }
}
