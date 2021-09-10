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

    public function __construct(?array $theme)
    {
        $this->theme = $theme;
        unset($this->theme['components'], $this->theme['style']);
    }

    public function process(Request $request, $handler) : Response
    {
        $response = $handler->handle($request);
        return $this->getResponse($response);
    }

    public function getResponse(Response $response) : Response
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
        $tailwind->addCallback('size', new \Flipsite\Style\Callbacks\ResponsiveSizeCallback($config['screens']));
        $tailwind->addCallback('background-image', new \Flipsite\Style\Callbacks\BgGradientCallback());

        $css           = $tailwind->getCss($elements, $classes);
        $css           = $this->compress($css);
        $search        = '</head>';
        $replace       = '  <style>'.$css."\n    ".'</style>'."\n  </head>";
        $htmlWithStyle = str_replace($search, $replace, $html);

        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($htmlWithStyle);
        return $response->withBody($stream);
    }

    private function compress(string $css) : string
    {
        $css  = str_replace(', ', ',', $css);
        $vars = [
            '--tw-gradient-from'       => '--a',
            '--tw-gradient-stops'      => '--b',
            '--tw-gradient-to'         => '--c',
            '--tw-bg-opacity'          => '--d',
            '--tw-border-opacity'      => '--e',
            '--tw-text-opacity'        => '--f',
            '--tw-placeholder-opacity' => '--g',
            '--tw-ring-opacity'        => '--h',
            '--tw-divide-opacity'      => '--i',
            '--tw-space-x-reverse'     => '--j',
            '--tw-space-y-reverse'     => '--k',
            '--tw-translate-x'         => '--l',
            '--tw-translate-y'         => '--m',
            '--tw-rotate'              => '--n',
            '--tw-skew-x'              => '--o',
            '--tw-skew-y'              => '--p',
            '--tw-scale-x'             => '--q',
            '--tw-scale-y'             => '--r',
            '--tw-backdrop-blur'       => '--s',
            '--tw-backdrop-brightness' => '--t',
            '--tw-backdrop-contrast'   => '--u',
            '--tw-backdrop-grayscale'  => '--v',
            '--tw-backdrop-hue-rotate' => '--x',
            '--tw-backdrop-invert'     => '--y',
            '--tw-backdrop-opacity'    => '--z',
            '--tw-backdrop-saturate'   => '--aa',
            '--tw-backdrop-sepia'      => '--ab',
            '--tw-blur'                => '--ac',
            '--tw-brightness'          => '--ad',
            '--tw-contrast'            => '--ar',
            '--tw-grayscale'           => '--af',
            '--tw-hue-rotate'          => '--ag',
            '--tw-invert'              => '--ah',
            '--tw-saturate'            => '--ai',
            '--tw-sepia'               => '--aj',
            '--tw-drop-shadow'         => '--ak',
            '--tw-divide-x-reverse'    => '--al',
            '--tw-divide-y-reverse'    => '--am',
        ];
        foreach ($vars as $search => $replace) {
            $css = str_replace($search, $replace, $css);
        }
        return $css;
    }
}
