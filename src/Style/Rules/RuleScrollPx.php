<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScrollPx extends AbstractRuleSpacing
{
    /**
     * @var array<string>
     */
    protected array $properties = ['scroll-padding-left', 'scroll-padding-right'];
}
