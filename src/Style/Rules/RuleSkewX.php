<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleSkewX extends AbstractRuleTransform
{
    protected array $properties = ['--tw-skew-x'];
    protected string $unit      = 'deg';
}
