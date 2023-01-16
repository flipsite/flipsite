<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\Environment;

trait EnvironmentTrait
{
    protected Environment $environment;

    public function addEnvironment(Environment $environment) : void
    {
        $this->environment = $environment;
    }
}
