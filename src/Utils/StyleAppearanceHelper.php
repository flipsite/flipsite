<?php

declare(strict_types=1);

namespace Flipsite\Utils;

final class StyleAppearanceHelper
{
    public static function apply(array $style, ?string $appearance = null) : array
    {
        $appearance ??= 'light';
        $dark = $style['dark'] ?? [];
        unset($style['dark']);
        if ('light' === $appearance) {
            return $style;
        }   
        $autoDark = new AutoDark();
        $dark = $autoDark->apply($dark, $style);
        if (!$dark) {
            return $style;
        }
        if ('dark' === $appearance && $dark) {
            return ArrayHelper::merge($style, $dark);
        } elseif ('auto' === $appearance && $dark) {
            return ArrayHelper::merge($style, ArrayHelper::addPrefix($dark, 'dark:'));
        }
        return $style;
    }
}

class AutoDark {
    public function apply(array $dark, array $style) : array {
        $attributes = ['textColor','borderColor'];
        foreach ($attributes as $attr) {
            if (!isset($dark[$attr]) && isset($style[$attr]) && is_string($style[$attr])) {
                $value = $this->getDark($style[$attr]);
                if ($value) {
                    $dark[$attr] = $value;
                }
            }   
        }
        if (!isset($dark['background']['color']) && isset($style['background']['color']) && is_string($style['background']['color'])) {
            $value = $this->getDark($style['background']['color']);
            if ($value) {
                $dark['background']['color'] = $value;
            }
        }
        return $dark;
    }
    private function getDark(string $style) : ?string {
        $pattern = '/(.*?)-l([1-9]|1[0-2])(.*)/';
        $replacement = '$1-d$2$3';
        $new = preg_replace($pattern, $replacement, $style);
        return $new === $style ? null : $new;
    }
}