<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleBackdropGrayscale extends AbstractRuleGrayscale
{
    /**
     * @var array<string>
     */
    protected array $properties = ['--tw-backdrop-grayscale'];

    protected bool $backdrop = true;
}
