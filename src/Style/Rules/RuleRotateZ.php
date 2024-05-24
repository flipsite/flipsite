<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleRotateZ extends AbstractRuleTransform
{
    protected array $properties = ['--tw-rotate-z'];
    protected string $unit      = 'deg';
}
