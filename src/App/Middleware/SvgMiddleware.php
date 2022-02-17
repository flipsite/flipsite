<?php

declare(strict_types=1);
namespace Flipsite\App\Middleware;

use Flipsite\Enviroment;
use Flipsite\Utils\SvgData;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Factory\StreamFactory;

class SvgMiddleware implements MiddlewareInterface
{
    private Enviroment $enviroment;

    public function __construct(Enviroment $enviroment)
    {
        $this->enviroment = $enviroment;
    }

    public function process(Request $request, $handler) : Response
    {
        $response = $handler->handle($request);
        $html     = (string) $response->getBody();
        $html     = str_replace("<svg></svg>\n", "<dummysvg></dummysvg>\n", $html);
        preg_match_all('/<svg(.|\n)*?src="(.*?)" xmlns(.|\n)*?<\/svg>/', $html, $matches);

        if (0 === count($matches[0] ?? [])) {
            $html = str_replace("    <dummysvg></dummysvg>\n", '', $html);
        } else {
            $defs = '';
            $id   = 0;
            $svgs = [];
            foreach ($matches[2] as $i => $src) {
                if (!isset($svgs[$src])) {
                    $file = $this->enviroment->getAssetSources()->getFilename($src);
                    if (null !== $file) {
                        $svgData    = new SvgData($file);
                        $svgs[$src] = [
                            'id'      => mb_substr(md5((string) $id), 0, 6),
                            'viewbox' => $svgData->getViewbox(),
                            'def'     => $svgData->getDef(),
                        ];
                        ++$id;
                        $def  = '<g id="'.$svgs[$src]['id'].'">';
                        $def .= $svgs[$src]['def'];
                        $def .= "</g>\n";
                        $def = preg_replace('!\s+!', ' ', $def);
                        $def = str_replace("\n", ' ', $def);
                        $defs .= '        '.$def."\n";
                    }
                }
                $with = $matches[0][$i];
                $with = str_replace(' src="'.$src.'"', '', $with);
                $with = str_replace('xlink:href=""', 'xlink:href="#'.$svgs[$src]['id'].'"', $with);
                $with = str_replace('viewBox=""', 'viewBox="'.$svgs[$src]['viewbox'].'"', $with);
                $html = str_replace($matches[0][$i], $with, $html);
            }
            $svg = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: none;">';
            $svg .= "\n      <defs>\n";
            $defs = str_replace('></path>', '/>', $defs);
            $svg .= $defs;
            $svg .= "      </defs>\n    </svg>";
            $html = str_replace("<dummysvg></dummysvg>\n", $svg."\n", $html);
        }
        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($html);
        return $response->withBody($stream);
    }
}
