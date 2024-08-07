<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScaleY extends AbstractRuleTransform
{
    protected array $properties = ['--tw-scale-y'];
    protected string $unit      = '%';
}
