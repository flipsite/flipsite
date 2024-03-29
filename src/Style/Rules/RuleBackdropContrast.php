<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleBackdropContrast extends AbstractRuleContrast
{
    /**
     * @var array<string>
     */
    protected array $properties = ['--tw-backdrop-contrast'];

    protected bool $backdrop = true;
}
