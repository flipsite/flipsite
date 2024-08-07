<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleRotateY extends AbstractRuleTransform
{
    protected array $properties = ['--tw-rotate-y'];
    protected string $unit      = 'deg';
}
