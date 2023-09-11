<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules\Traits;

use Flipsite\Utils\ColorHelper;
use SSNepenthe\ColorUtils\Colors\Color;

trait ColorTrait
{
    protected function setColor(array $args, string $property): bool
    {
        $color = $this->getColor($args);
        if (null === $color) {
            return false;
        }
        $this->setDeclaration($property, (string) $color);
        return true;
    }

    protected function getColor(array $args): ?Color
    {
        return ColorHelper::getColor($args, $this->getConfig('colors'));
    }
}
