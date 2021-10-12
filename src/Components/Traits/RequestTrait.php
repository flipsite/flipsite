<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

use Psr\Http\Message\ServerRequestInterface as Request;

trait RequestTrait
{
    protected Request $request;

    public function addRequest(Request $request) : void
    {
        $this->request = $request;
    }
}
