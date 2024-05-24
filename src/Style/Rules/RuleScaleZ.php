<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScaleZ extends AbstractRuleTransform
{
    protected array $properties = ['--tw-scale-z'];
    protected string $unit      = '%';
}
