<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

use SSNepenthe\ColorUtils\Colors\ColorFactory;

final class RuleFrom extends AbstractRule
{
    use Traits\ColorTrait;

    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $color = $this->getColor($args);
        if ($color) {
            $this->setDeclaration('--tw-gradient-from', (string) $color.'!important');
            $rgb          = $color->getRgb()->toArray();
            $rgb['alpha'] = 0;
            $transparent  = ColorFactory::fromArray($rgb);
            $this->setDeclaration('--tw-gradient-stops', 'var(--tw-gradient-from),var(--tw-gradient-to,'.$transparent.')!important');
        }
    }
}
