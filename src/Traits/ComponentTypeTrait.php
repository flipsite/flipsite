<?php

declare(strict_types=1);
namespace Flipsite\Traits;

trait ComponentTypeTrait
{
    protected function getComponentType(string $type) : ?string
    {
        $flags = explode(':', $type);
        $type  = array_shift($flags);

        // Fallback
        $fallback = ['container', 'logo', 'button', 'link', 'toggle', 'question'];
        if (in_array($type, $fallback)) {
            $type = 'group';
        }

        $class = 'Flipsite\\Components\\' . ucfirst($type);
        return class_exists($class) ? $type : null;
    }
}
