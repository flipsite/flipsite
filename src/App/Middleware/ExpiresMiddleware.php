<?php

declare(strict_types=1);

namespace Flipsite\App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;

class ExpiresMiddleware implements MiddlewareInterface
{
    private int $offset;

    public function __construct(int $offset)
    {
        $this->offset = $offset;
    }

    public function process(Request $request, $handler) : Response
    {
        $response = $handler->handle($request);
        $now      = gmstrftime('%a, %d %b %Y %H:%M:%S GMT', time());
        $response = $response->withHeader('Last-Modified', $now);
        $nextYear = gmstrftime('%a, %d %b %Y %H:%M:%S GMT', time() + $this->offset);
        return $response->withHeader('Expires', $nextYear);
    }
}
