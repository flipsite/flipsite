<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractElement
{
    protected int|string|null $id       = null;
    protected bool $empty               = false;
    protected bool $oneline             = false;
    protected string $tag               = '';
    protected array $children           = [];
    protected string $content           = '';
    protected array $attributes         = [];
    protected array $style              = [];
    protected array $css                = [];
    protected bool $render              = true;
    private ?string $cache              = null;
    private ?string $commentOut         = null;
    private ?string $commentBefore      = null;
    private ?string $commentAfter       = null;
    private bool $wrap                  = true;
    private array $meta               = [];

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function setId(int|string|null $id)
    {
        $this->id = $id;
    }

    public function setMeta(string $key, $value): void
    {
        $this->meta[$key] = $value;
    }
    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    public function setWrap(bool $wrap): void
    {
        $this->wrap = $wrap;
    }

    public function getStyle(): array
    {
        return $this->style;
    }

    public function getDefaultStyle(): array
    {
        return [];
    }

    public function addStyle(null|array|string $style): self
    {
        if (null === $style) {
            return $this;
        }
        if (is_string($style)) {
            $style = ['_' => $style];
        }
        foreach ($style as $attr => $classes) {
            if (is_string($classes)) {
                $this->style[$attr] ??= [];
                $this->style[$attr] = array_merge($this->style[$attr], explode(' ', $classes));
            }
        }
        $this->cache = null;
        return $this;
    }

    public function addCss(string $attribute, string $value): self
    {
        $this->css[$attribute] = $value;
        $this->cache           = null;
        return $this;
    }

    public function getCss(?string $attribute = null): array|string|null
    {
        if (null === $attribute) {
            return $this->css;
        }
        return $this->css[$attribute] ?? null;
    }

    public function hasCss(string $attribute): bool
    {
        return isset($this->css[$attribute]);
    }

    public function replaceStyle(array $style)
    {
        return $this->style = $style;
    }

    public function commentOut(bool $commentOut, string $comment)
    {
        $this->commentOut = $commentOut ? $comment : null;
    }

    public function addCommentBefore(string $comment)
    {
        $this->commentBefore = $comment;
    }

    public function addCommentAfter(string $comment)
    {
        $this->commentAfter = $comment;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function childCount(): int
    {
        return count($this->children);
    }

    public function hasAttribute(string $attribute): bool
    {
        return isset($this->attributes[$attribute]);
    }

    public function attributeCount(): int
    {
        return count($this->attributes) + count($this->style) ? 1 : 0;
    }

    public function getChild(string $name): ?AbstractElement
    {
        return $this->children[$name] ?? null;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttribute(string $attribute, $value, bool $append = false): self
    {
        $this->cache = null;
        if (null === $value) {
            unset($this->attributes[$attribute]);
        } elseif ($append && isset($this->attributes[$attribute])) {
            $this->attributes[$attribute] .= ' '.$value;
        } else {
            $this->attributes[$attribute] = $value;
        }
        return $this;
    }

    public function setAttributes(array $attributes, bool $append = false): self
    {
        foreach ($attributes as $attribute => $value) {
            $this->setAttribute($attribute, $value, $append);
        }
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->cache   = null;
        if (is_string($content)) {
            $this->content = $content;
        }
        return $this;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    public function setRender(bool $render): self
    {
        $this->render = $render;
        return $this;
    }

    public function appendContent(string $content): self
    {
        $this->cache = null;
        $this->content .= $content;
        return $this;
    }

    public function prependChild(?AbstractElement $child = null, ?string $name = null): self
    {
        if (null === $child) {
            return $this;
        }
        $this->cache = null;
        $children    = [];
        if (null !== $name) {
            $children[$name] = $child;
        } else {
            $children[] = $child;
        }
        $this->children = array_merge($children, $this->children);
        return $this;
    }

    public function addChild(?AbstractElement $child = null, ?string $name = null): self
    {
        $this->cache = null;
        if (null === $child) {
            return $this;
        }
        if (null !== $name) {
            $this->children[$name] = $child;
        } else {
            $this->children[] = $child;
        }
        return $this;
    }

    public function removeChild(?string $name = null): self
    {
        if (null === $name) {
            return $this;
        }
        unset($this->children[$name]);
        return $this;
    }

    public function purgeChildren(): self
    {
        $this->cache    = null;
        $this->children = [];
        return $this;
    }

    public function replaceChildren(array $children): self
    {
        $this->cache    = null;
        $this->children = $children;
        return $this;
    }

    public function addChildren(array $children): self
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
        return $this;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false): ?string
    {
        if (!$this->render) {
            return null;
        }
        if ($this->cache) {
            return $this->cache;
        }

        if (!$this->content && !count($this->children) && !$this->empty) {
            $this->oneline = true;
        }

        if (count($this->css)) {
            $this->setAttribute('style', implode('; ', array_map(
                fn ($key, $value) => "$key: $value",
                array_keys($this->css),
                $this->css
            )));
        }
        //$this->purgeInvalidAttributes();
        $html = '';
        $i    = str_repeat(' ', $indentation * $level);
        if ($this->commentBefore) {
            $html .= $i.'<!-- '.$this->commentBefore.' -->'."\n";
        }
        if ('' === $this->tag && $this->wrap) {
            $html .= $i.wordwrap($this->content, 80, $i."\n");
            $html .= "\n";
            return $html;
        }
        $html .= $i.'<'.$this->tag.$this->renderAttributes().'>';
        if ($this->empty) {
            return $html."\n";
        }
        if (!$this->oneline && !$oneline) {
            $html .= "\n";
        }
        if (count($this->children)) {
            foreach ($this->children as $name => $child) {
                $html .= $child->render($indentation, $level + 1);
            }
        } else {
            if (!$this->oneline && !$oneline) {
                $html .= $this->renderContent($indentation, $level + 1, $this->content);
            } else {
                $html .= $this->content;
            }
        }
        if (!$this->oneline && !$oneline) {
            $html .= $i.'</'.$this->tag.'>'."\n";
        } else {
            $html .= '</'.$this->tag.'>'."\n";
        }
        if ($this->commentAfter) {
            $html .= $i.'<!-- '.$this->commentAfter.' -->'."\n";
        }

        if ($this->commentOut) {
            $html = $i.'<!-- '.$this->commentOut."\n".$html.$i.'-->'."\n";
        }
        return $this->cache = $html;
    }

    protected function renderContent(int $indentation, int $level, string $content): string
    {
        $i = str_repeat(' ', $indentation * $level);
        if ($this->wrap) {
            return $i.wordwrap($content, 120, "\n".$i)."\n";
        } else {
            return $i.$content."\n";
        }
    }

    public function renderAttributes(): string
    {
        if (count($this->style)) {
            $nonStyleClasses = $this->getAttribute('class');
            $class           = $this->getClasses();
            if ($nonStyleClasses) {
                $classes = ArrayHelper::decodeJsonOrCsv($nonStyleClasses);
                $class .= ' '.implode(' ', $classes);
                $class = trim($class);
            }
            $this->setAttribute('class', $class);
        }
        $html = '';
        foreach ($this->attributes as $attribute => $value) {
            if (is_bool($value) && !str_starts_with($attribute, 'aria-')) {
                if ($value) {
                    $html .= ' '.$attribute;
                } else {
                    //$html .= ' '.$attribute.'="false"';
                }
            } else {
                if (is_bool($value) && $value) {
                    $value = 'true';
                } elseif (is_bool($value) && !$value) {
                    $value = 'false';
                } elseif (is_array($value)) {
                    $value = htmlentities(json_encode($value), ENT_QUOTES, 'UTF-8');
                }
                if (strpos((string)$value, '"') !== false) {
                    $value = str_replace("'", "\'", $value);
                    $html .= ' '.$attribute."='".$value."'";
                } else {
                    $html .= ' '.$attribute.'="'.$value.'"';
                }
            }
        }
        return $html;
    }

    public function getClasses(string $format = 'string'): string|array
    {
        $allClasses = [];
        foreach ($this->style as $attribute => $classes) {
            if ($attribute[0] === '_') {
                continue;
            }
            $allClasses  = array_merge($allClasses, $classes);
        }
        $allClasses = array_unique($allClasses);
        sort($allClasses);
        return 'string' === $format ? trim(implode(' ', $allClasses)) : $allClasses;
    }

    public function forEach(callable $callback): void
    {
        $callback($this);
        foreach ($this->children as $child) {
            $child->forEach($callback);
        }
    }

    private function purgeInvalidAttributes(): void
    {
        $allowed = ['onclick', 'style'];
        switch ($this->tag) {
            case 'button':
                $allowed = array_merge(
                    $allowed,
                    ['disabled', 'form', 'formaction', 'formenctype', 'formmethod',
                        'formnovalidate', 'formtarget', 'name', 'type', 'value', ]
                );
                break;
            default:
                return;
        }

        $purge = [];
        foreach ($this->attributes as $attributeibute => $value) {
            if (!in_array($attributeibute, $allowed) && !str_starts_with($attributeibute, 'data-') && !str_starts_with($attributeibute, 'aria-')) {
                $purge[] = $attributeibute;
            }
        }
        foreach ($purge as $purgeAttribute) {
            unset($this->attributes[$purgeAttribute]);
        }
    }
}
