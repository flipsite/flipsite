<?php

declare(strict_types=1);

namespace Flipsite\App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Factory\StreamFactory;

class DiagnosticsMiddleware implements MiddlewareInterface
{
    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function process(Request $request, $handler) : Response
    {
        $timeEnd  = microtime(true);
        $response = $handler->handle($request);
        $html     = (string) $response->getBody();
        $duration = ($timeEnd - $this->startTime) / 60;

        // $html .= '<!-- '.$duration.'s -->';
        // $html = str_replace("\n", '', $html);
        // $html = preg_replace('/\s+/', ' ', $html);
        // $html = str_replace('> <', '><', $html);
        // $html = str_replace('> ', '>', $html);
        // $html = str_replace(' <', '<', $html);

        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($html);
        return $response->withBody($stream);
    }
}
