<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use Flipsite\Enviroment;

trait EnviromentTrait
{
    protected Enviroment $enviroment;

    public function addEnviroment(Enviroment $enviroment) : void
    {
        $this->enviroment = $enviroment;
    }
}
