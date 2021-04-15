<?php

declare(strict_types=1);

namespace Flipsite\App\Middleware;

use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;

class OfflineMiddleware implements MiddlewareInterface
{
    private Enviroment $enviroment;
    private Reader $reader;

    public function __construct(Enviroment $enviroment, Reader $reader)
    {
        $this->enviroment = $enviroment;
        $this->reader     = $reader;
    }

    public function process(Request $request, $handler) : Response
    {
        if (!$this->reader->isOnline()) {
            $response = new \Slim\Psr7\Response();
            $msg      = $this->enviroment->getServer(false).' offline';
            $response->getBody()->write('<pre>'.$msg.'</pre>');
            return $response;
        }
        return $handler->handle($request);
    }
}
