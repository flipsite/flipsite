<?php

declare(strict_types=1);
namespace Flipsite\Utils;

final class FormValidator
{
    public static function validate(array $inputs, array $required, array $dummy, array $data) : bool
    {
        foreach ($dummy as $var) {
            if (isset($data[$var]) && strlen($data[$var]) > 0) {
                return false;
            }
        }
        return true;
    }
}
