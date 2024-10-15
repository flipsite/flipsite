<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleSkewY extends AbstractRuleTransform
{
    protected array $properties = ['--tw-skew-y'];
    protected string $unit      = 'deg';
}
