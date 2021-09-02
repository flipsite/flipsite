<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class StyleAppearanceHelper
{
    public static function apply(array $style, ?string $appearance = null) : array
    {
        $appearance ??= 'light';
        $dark = $style['dark'] ?? null;
        unset($style['dark']);
        if ('dark' === $appearance && $dark) {
            return ArrayHelper::merge($style, $dark);
        } elseif ('auto' === $appearance && $dark) {
            return ArrayHelper::merge($style, ArrayHelper::addPrefix($dark, 'dark:'));
        }
        return $style;
    }
}
