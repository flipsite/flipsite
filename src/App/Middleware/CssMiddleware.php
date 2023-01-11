<?php

declare(strict_types=1);

namespace Flipsite\App\Middleware;

use Flipsite\Style\Parsers\HtmlParser;
use Flipsite\Style\Tailwind;
use Flipsite\Utils\ArrayHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Factory\StreamFactory;
use Symfony\Component\Yaml\Yaml;

class CssMiddleware implements MiddlewareInterface
{
    private ?array $theme;

    public function __construct(?array $theme, private \Flipsite\Utils\CanIUse $canIUse)
    {
        $this->theme = $theme;
        unset($this->theme['components'], $this->theme['style']);
    }

    public function process(Request $request, $handler): Response
    {
        $response = $handler->handle($request);
        return $this->getResponse($response);
    }

    public function getResponse(Response $response): Response
    {
        $html = (string) $response->getBody();

        $elements = HtmlParser::getElements($html, ['head', 'title', 'meta', 'script', 'link', 'style']);
        $classes  = HtmlParser::getClasses($html);

        $config = Yaml::parse(file_get_contents(__DIR__.'/../../Style/config.yaml'));

        // Overwrite keyframe definitions instead of merge
        foreach ($this->theme['keyframes'] ?? [] as $keyframe => $definition) {
            if (isset($config['keyframes'][$keyframe])) {
                $config['keyframes'][$keyframe] = $definition;
                unset($config['keyframes'][$keyframe]);
            }
        }
        $config = ArrayHelper::merge($config, $this->theme ?? []);
        $fonts  = $config['fonts'] ?? [];

        unset($config['fonts']);
        if (!isset($config['fontFamily'])) {
            $config['fontFamily'] = [];
        }

        foreach ($fonts as $type => $options) {
            if (!is_array($options)) {
                continue;
            }
            $font = $options['family'];
            if (false !== mb_strpos($font, ' ')) {
                $font = "'".$font."'";
            }
            $font                        = [$font];
            $fallback                    = $options['fallback'] ?? 'sans';
            $font                        = array_merge($font, $config['fontFamily'][$fallback] ?? []);
            $config['fontFamily'][$type] = $font;
        }

        $tailwind = new Tailwind($config);
        $tailwind->addCallback('size', new \Flipsite\Style\Callbacks\UnitCallback());
        $tailwind->addCallback('size', new \Flipsite\Style\Callbacks\ResponsiveSizeCallback($config['screens'], $this->canIUse->cssMathFunctions()));
        $tailwind->addCallback('background-image', new \Flipsite\Style\Callbacks\BgGradientCallback());

        $css           = $tailwind->getCss($elements, $classes);
        $htmlWithStyle = str_replace('<style></style>', '<style>'.$css."\n    ".'</style>', $html);

        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($htmlWithStyle);
        return $response->withBody($stream);
    }
}
