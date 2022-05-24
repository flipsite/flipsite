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
        $now      = gmdate('D, d M Y H:i:s \G\M\T', time());
        $response = $response->withHeader('Last-Modified', $now);
        $nextYear = gmdate('D, d M Y H:i:s \G\M\T', time() + $this->offset);
        return $response->withHeader('Expires', $nextYear);
    }
}
