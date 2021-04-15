<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTextStroke extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $color = $this->getColor($args);
        if ($color) {
            $this->setDeclaration('-webkit-text-stroke-color', (string) $color);
            return;
        }
        $this->setDeclaration('-webkit-text-stroke-width', intval($args[0]).'px');
    }
}
