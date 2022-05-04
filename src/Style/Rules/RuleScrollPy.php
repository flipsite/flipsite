<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScrollPy extends AbstractRuleSpacing
{
    /**
     * @var array<string>
     */
    protected array $properties = ['scroll-padding-top', 'scroll-padding-bottom'];
}
