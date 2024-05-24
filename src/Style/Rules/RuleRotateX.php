<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleRotateX extends AbstractRuleTransform
{
    protected array $properties = ['--tw-rotate-x'];
    protected string $unit      = 'deg';
}
