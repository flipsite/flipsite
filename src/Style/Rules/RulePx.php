<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RulePx extends AbstractRuleSpacing
{
    /**
     * @var array<string>
     */
    protected array $properties = ['padding-left', 'padding-right'];
}
