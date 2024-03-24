<?php

declare(strict_types=1);

namespace Flipsite\Style;

use Flipsite\Style\Rules\AbstractRule;
use Symfony\Component\Yaml\Yaml;

final class Tailwind implements CallbackInterface
{
    private string $dark = 'media';
    private array $rules;
    private array $variants  = [];
    private array $callbacks = [];

    public function __construct(private array $config, private array $themeSettings = [])
    {
        $this->rules  = Yaml::parse(file_get_contents(__DIR__.'/rules.yaml'));
    }

    public function call(string $property, array $args): ?string
    {
        if (!isset($this->callbacks[$property])) {
            return null;
        }

        foreach ($this->callbacks[$property] as $callback) {
            $value = $callback($args);
            if (null !== $value) {
                return $value;
            }
        }
        return null;
    }

    public function addCallback(string $property, callable $callback): self
    {
        if (!isset($this->callbacks[$property])) {
            $this->callbacks[$property] = [];
        }
        $this->callbacks[$property][] = $callback;
        return $this;
    }

    public function getCss(array $elements, array $classes): string
    {
        $hasBorders = $this->hasBorders($elements, $classes);
        $css        = $this->getPreflight(
            $elements,
            $this->config['fontFamily']['sans'] ?? null,
            $this->config['fontFamily']['mono'] ?? null,
            $hasBorders,
            $this->config['borderColor']['DEFAULT'],
            $this->config['borderWidth']['DEFAULT'] ?? '1px',
        );
        foreach ($classes as $class) {
            $variant = ['DEFAULT'];
            if (mb_strpos($class, ':')) {
                $variant = explode(':', $class);
                $class   = array_pop($variant);
            }
            $this->addToVariant($variant, $class);
        }
        uasort($this->variants, function ($a, $b) {
            return $a->order() <=> $b->order();
        });
        foreach ($this->variants as $variant) {
            $css .= $variant->getCss();
        }

        // Keyframes
        $pattern = '/animation\:([a-z0-9\-\_]+)\s{1}/';
        $matches = [];
        preg_match_all($pattern, $css, $matches);
        if (count($matches[1])) {
            $css .= $this->addKeyframes($matches[1]);
        }

        // Fix tranform vars
        $transform = [
            'perspective' => 'perspective',
            'translateX' => 'translate-x',
            'translateY' => 'translate-y',
            'rotate' => 'rotate',
            'rotateX' => 'rotate-x',
            'rotateY' => 'rotate-y',
            'skewX' => 'skew-x',
            'skewY' => 'skew-y',
            'scaleX' => 'scale-x',
            'scaleY' => 'scale-y',
        ];
        foreach ($transform as $func => $var) {
            if (strpos($css, '-tw-'.$var.':') === false) {
                $css = str_replace($func.'(var(--tw-'.$var.'))', '', $css);
            }
        }
        $css = preg_replace('/\s+/', ' ', $css);

        // Optimize vars
        $matches = [];
        preg_match_all('/--tw-[a-z\-]+/', $css, $matches);
        $addDefaultValues = [];
        $vars = [];
        foreach (array_unique($matches[0]) as $i => $var) {
            $vars[] = $var;
        }
        usort($vars, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($vars as $i => $var) {   
            $addDefaultValues[$var] = '--'.$this->getVar($i);
            $css                    = str_replace($var, '--'.$this->getVar($i), $css);
        }
        if (count($addDefaultValues)) {
            $css = $this->addDefaultValues($css, $addDefaultValues);
        }

        return $css;
    }

    private function addDefaultValues(string $css, array $addDefaultValues)
    {
        $default       = '';
        $defaultValues = [
            '--tw-border-spacing-x'       => 0,
            '--tw-border-spacing-y'       => 0,
            '--tw-translate-x'            => 0,
            '--tw-translate-y'            => 0,
            '--tw-rotate'                 => 0,
            '--tw-rotate-x'               => 0,
            '--tw-rotate-y'               => 0,
            '--tw-perspective'            => 'none',
            '--tw-skew-x'                 => 0,
            '--tw-skew-y'                 => 0,
            '--tw-scale-x'                => 1,
            '--tw-scale-y'                => 1,
            '--tw-pan-x'                  => '',
            '--tw-pan-y'                  => '',
            '--tw-pinch-zoom'             => '',
            '--tw-scroll-snap-strictness' => 'proximity',
            '--tw-ordinal'                => '',
            '--tw-slashed-zero'           => '',
            '--tw-numeric-figure'         => '',
            '--tw-numeric-spacing'        => '',
            '--tw-numeric-fraction'       => '',
            '--tw-ring-inset'             => '',
            '--tw-ring-offset-width'      => '0px',
            '--tw-ring-offset-color'      => '#fff',
            '--tw-ring-color'             => 'rgb(59 130 246 / 0.5)',
            '--tw-ring-offset-shadow'     => '0 0 #0000',
            '--tw-ring-shadow'            => '0 0 #0000',
            '--tw-shadow'                 => '0 0 #0000',
            '--tw-shadow-colored'         => '0 0 #0000',
            '--tw-blur'                   => '',
            '--tw-brightness'             => '',
            '--tw-contrast'               => '',
            '--tw-grayscale'              => '',
            '--tw-hue-rotate'             => '',
            '--tw-invert'                 => '',
            '--tw-saturate'               => '',
            '--tw-sepia'                  => '',
            '--tw-drop-shadow'            => '',
            '--tw-backdrop-blur'          => '',
            '--tw-backdrop-brightness'    => '',
            '--tw-backdrop-contrast'      => '',
            '--tw-backdrop-grayscale'     => '',
            '--tw-backdrop-hue-rotate'    => '',
            '--tw-backdrop-invert'        => '',
            '--tw-backdrop-opacity'       => '',
            '--tw-backdrop-saturate'      => '',
            '--tw-backdrop-sepia'         => '',
        ];
        foreach ($addDefaultValues as $var => $optimized) {
            if (isset($defaultValues[$var])) {
                $default .= $optimized.':'.$defaultValues[$var].';';
            }
        }
        return str_replace('*,::before,::after{', '*,::before,::after{'.$default, $css);
    }

    public function getRules(string $className): null|string|AbstractRule
    {
        if (isset($this->rules[$className])) {
            return $this->rules[$className];
        }
        $class = $this->getRule($className);
        if (null === $class) {
            return null;
        }
        return $class;
    }

    private function hasBorders(array $elements, array $classes): bool
    {
        if (in_array('hr', $elements)) {
            return true;
        }
        foreach ($classes as $class) {
            if (false !== mb_strpos($class, 'border')) {
                return true;
            }
        }
        return false;
    }

    private function getRule(string $className): ?AbstractRule
    {
        if (!mb_strlen($className)) {
            return null;
        }
        $negative = false;
        if (strpos($className,'[-') !== false) {
            $className = '-'.str_replace('[-','[',$className);
        }

        $args     = explode('-', $className);
        if ('' === $args[0]) { //negative
            array_shift($args);
            $negative = true;
        }
        if (!isset($args[0])) {
            return null;
        }
        $first = ucfirst($args[0]);
        if (isset($args[2])) {
            $third     = ucfirst((string) $args[2]);
            $second    = ucfirst((string) $args[1]);
            $ruleClass = 'Flipsite\Style\Rules\Rule'.$first.$second.$third;
            if (class_exists($ruleClass)) {
                array_shift($args);
                array_shift($args);
                array_shift($args);
                return new $ruleClass($args, $negative, $this->config, $this->themeSettings, $this);
            }
        }
        if (isset($args[1])) {
            $second    = ucfirst((string) $args[1]);
            $ruleClass = 'Flipsite\Style\Rules\Rule'.$first.$second;
            if (class_exists($ruleClass)) {
                array_shift($args);
                array_shift($args);
                return new $ruleClass($args, $negative, $this->config, $this->themeSettings, $this);
            }
        }
        $ruleClass = 'Flipsite\Style\Rules\Rule'.$first;
        if (class_exists($ruleClass)) {
            array_shift($args);
            return new $ruleClass($args, $negative, $this->config, $this->themeSettings, $this);
        }
        return null;
    }

    private function addToVariant(array $variants, string $class): void
    {
        $variantId = implode(':', $variants);
        if (!isset($this->variants[$variantId])) {
            $this->variants[$variantId] = $this->createVariant($variants);
        }

        $this->variants[$variantId]->addClass($class);
    }

    private function getPreflight(array $elements, ?array $sansFonts = null, ?array $monoFonts = null, bool $hasBorders = false, ?string $borderColor = null, ?string $borderWidth = null)
    {
        $css = '*,::before,::after{box-sizing:border-box;';
        if ($hasBorders) {
            $css .= 'border-width:0;border-style:solid;';
            $css .= 'border-color:'.$borderColor;
        }
        $css .= '}';
        $preflight = new Preflight();
        $css .= $preflight->getCss($elements);
        $css = str_replace('fontFamily.sans', implode(',', $sansFonts), $css);
        $css = str_replace('fontFamily.mono', implode(',', $monoFonts), $css);
        if ($hasBorders) {
            $css = str_replace('borderWidth.DEFAULT', $borderWidth, $css);
        }
        return $css;
    }

    private function createVariant(array $variants): Variant
    {
        $variant = new Variant($this);
        foreach ($variants as $variantId) {
            if ('DEFAULT' === $variantId) {
                continue;
            }
            if (isset($this->config['screens'][$variantId])) {
                $type = new Variants\ResponsiveType($variantId, intval($this->config['screens'][$variantId]));
            } elseif ('dark' === $variantId) {
                $type = new Variants\DarkType($this->dark);
            } else {
                // Change foo-bar to FooBar
                $variantId = explode('-', $variantId);
                foreach ($variantId as &$word) {
                    $word = ucfirst(mb_strtolower($word));
                }
                
                $variantId = implode('', $variantId);
                // To extract possible group hover name
                $tmp = explode('/',$variantId);
                $variantId = array_shift($tmp);
                $class     = 'Flipsite\Style\Variants\\'.$variantId.'Type';
                if (!class_exists($class)) {
                    $class     = 'Flipsite\Style\Variants\\CustomStateType';
                }
                $type = new $class($variantId, $tmp[0] ?? null);
            }
            $variant->addType($type);
        }
        return $variant;
    }

    private function addKeyframes(array $keyframes): string
    {
        $css = '';
        foreach ($keyframes as $keyframe) {
            $css .= ' @keyframes '.$keyframe.'{';
            foreach ($this->config['keyframes'][$keyframe] as $at => $properties) {
                $css .= $at.'{';
                $declarations = [];
                foreach ($properties as $attr => $value) {
                    $declarations[] = $attr.':'.$value;
                }
                $css .= implode(';', $declarations);
                $css .= '}';
            }

            $css .= '}';
        }
        return $css;
    }

    private function getVar(int $index): string
    {
        $index+=1;
        $label = '';
        // Convert the index to a base-26 representation
        while ($index > 0) {
            $remainder = ($index - 1) % 26;
            $label = chr(65 + $remainder) . $label;
            $index = intval(($index - $remainder) / 26);
        }
        return strtolower($label);
    }
}
