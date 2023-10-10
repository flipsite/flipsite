<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleShadow extends AbstractRule
{
    use Traits\ColorTrait;
    /**
     * @param array<string> $args
     */

    protected array $shadows = [
        'sm' => [
            '--tw-shadow' => '0 1px 2px 0 var(--tw-shadow-color)',
        ],
        'base' => [
            '--tw-shadow' => '0 1px 3px 0 var(--tw-shadow-color), 0 1px 2px -1px var(--tw-shadow-color)',
        ],
        'md' => [
            '--tw-shadow' => '0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color)',
        ],
        'lg' => [
            '--tw-shadow' => '0 10px 15px -3px var(--tw-shadow-color), 0 4px 6px -4px var(--tw-shadow-color)',
        ],
        'xl' => [
            '--tw-shadow' => '0 20px 25px -5px var(--tw-shadow-color), 0 8px 10px -6px var(--tw-shadow-color)',
        ],
        '2xl' => [
            '--tw-shadow' => '0 25px 50px -12px var(--tw-shadow-color)',
        ],
        'inner' => [
            '--tw-shadow' => 'inset 0 2px 4px 0 var(--tw-shadow-color)',
        ],
        'none' => [
            '--tw-shadow' => '0 0 #0000',
        ],
    ];

    protected function process(array $args): void
    {
        $color = $this->getColor($args);
        if ($color) {
            $this->setDeclaration('--tw-shadow-color', (string)$color);
            $this->order = 110;
            return;
        }
        $shadow =count($args) === 0 ? 'base' : $args[0];
        if (isset($this->shadows[$shadow])) {
            foreach ($this->shadows[$shadow] as $attr => $value) {
                $this->setDeclaration($attr, $value);
            }
            $this->order = 100;
            $this->setDeclaration('box-shadow', 'var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)');
        }
    }
}
