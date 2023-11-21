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
        $tailwind->addCallback('size', new \Flipsite\Style\Callbacks\ScreenWidthCallback($config['screens']));
        $tailwind->addCallback('size', new \Flipsite\Style\Callbacks\ResponsiveSizeCallback($config['screens'], true));

        $css           = $tailwind->getCss($elements, $classes);
        $newClasses = [];
        // $css = $this->minmizeClasses($css, $classes, $newClasses);
        // $html = $this->replaceClasses($html, $newClasses);
        $htmlWithStyle = str_replace('<style></style>', '<style>'.$css."\n    ".'</style>', $html);
        $streamFactory = new StreamFactory();
        $stream        = $streamFactory->createStream($htmlWithStyle);
        return $response->withBody($stream);
    }

    private function minmizeClasses(string $css, array $classes, array &$newClasses): string
    {
        usort($classes, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        $escape = ["/",'|','.',':','%','[',']'];
        foreach ($classes as $i => $class) {
            if (strpos($class,'open:') !== false) {
                continue;
            }
            $orginal = $class;
            $tmp = explode(':',$class);
            $prefix = false;
            if (count($tmp)>1) {
                $class = array_pop($tmp);
                $prefix = implode(':',$tmp);
            }
            $newClassName = $this->getClassName($i+1);
            if ($prefix) {
                $newClassName = $prefix.':'.$newClassName;
                $oldInCss = $prefix.':'.$class;
            } else {
                $oldInCss = $class;
            }
            $newInCss = $newClassName;

            $newClasses[$oldInCss] = $newInCss;

            foreach ($escape as $e) {
                $oldInCss = str_replace($e,'\\'.$e,$oldInCss);
                $newInCss = str_replace($e,'\\'.$e,$newInCss);
            }
    
            $css = str_replace('.'.$oldInCss,'.'.$newInCss, $css);
        }
        return $css;
    }

    private function getClassName(int $index): string
    {
        $label = '';

        // Convert the index to a base-26 representation
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $label = chr(65 + $remainder) . $label;
            $index = intval(($index - $remainder) / 26);
        }

        return strtolower($label);
    }
    private function replaceClasses(string $html, array $newClasses): string
    {
        $pattern = '/class=["\'](.*?)["\']/';
        preg_match_all($pattern, $html, $matches);
        $classValues = $matches[1];
        usort($classValues, function($a, $b) {
            return strlen($b) - strlen($a);
        });
        $states = ['open:','!open:'];
        foreach ($classValues as $class) {
            $new = [];
            $tmp = explode(' ',$class);
            foreach ($tmp as $oldClass) {
                $prefix = '';
                foreach ($states as $state) {
                    if (str_starts_with($oldClass,$state)) {
                        $prefix = $state;
                        $oldClass = substr($oldClass,strlen($prefix));
                    }
                }

                if (isset($newClasses[$oldClass])) {
                    $new[] = $prefix.$newClasses[$oldClass];
                } else {
                    $new[] = $oldClass;
                }
            }
            $html = str_replace('class="'.$class.'"','class="'.implode(' ',$new).'"', $html);
        }
        return $html;
    }
}
