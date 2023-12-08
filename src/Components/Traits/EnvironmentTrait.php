<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Flipsite\EnvironmentInterface;

trait EnvironmentTrait
{
    protected EnvironmentInterface $environment;

    public function addEnvironment(EnvironmentInterface $environment) : void
    {
        $this->environment = $environment;
    }
}
