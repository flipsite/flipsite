<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleScale extends AbstractRuleTransform
{
    protected array $properties = ['--tw-scale-x', '--tw-scale-y', '--tw-scale-z'];
    protected string $unit      = '%';
}
