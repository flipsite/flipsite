<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleTranslateZ extends AbstractRuleTransform
{
    protected array $properties = ['--tw-translate-z'];
    protected array $callbacks  = ['size'];
}
