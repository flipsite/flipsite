<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

use SSNepenthe\ColorUtils\Colors\ColorFactory;

final class RuleVia extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $color = $this->getColor($args);
        if ($color) {
            $rgb          = $color->getRgb()->toArray();
            $rgb['alpha'] = 0;
            $transparent  = ColorFactory::fromArray($rgb);
            $this->setDeclaration('--tw-gradient-stops', 'var(--tw-gradient-from),'.$color.',var(--tw-gradient-to,'.$transparent.')!important');
        }
    }
}
