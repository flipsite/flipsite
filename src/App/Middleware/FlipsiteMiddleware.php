<?php

declare(strict_types=1);

namespace Flipsite\App\Middleware;

use Flipsite\Data\Reader;
use Flipsite\Enviroment;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Flipsite\Utils\YamlExpander;

class FlipsiteMiddleware implements MiddlewareInterface
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function process(Request $request, $handler) : Response
    {
        if (str_starts_with($request->getUri()->getPath(), '/flipsite')) {
            $theme = $this->reader->get('theme');
            $yaml = YamlExpander::parseFile(__DIR__.'/../../../docs/site.yaml');
            $yaml['theme'] = $theme;
            $this->reader->loadSite($yaml);
        }
        return $handler->handle($request);
    }
}
