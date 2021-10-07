<?php

declare(strict_types=1);
namespace Flipsite\Utils;

use Respect\Validation\Validator as v;

final class FormValidator
{
    public static function validate(array $inputs, array $required, array $data) : bool
    {
        return true;
        //v::email()->validate('alexandre@gaigalas.net')
    }
}
