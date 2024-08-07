<?php

declare(strict_types=1);
namespace Flipsite\Style\Rules;

final class RuleTranslateX extends AbstractRuleTransform
{
    protected array $properties = ['--tw-translate-x'];
    protected array $callbacks  = ['size'];
}
