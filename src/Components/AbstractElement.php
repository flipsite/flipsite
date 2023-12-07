<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractElement
{
    protected bool $empty       = false;
    protected bool $oneline     = false;
    protected string $tag       = '';
    protected array $children   = [];
    protected string $content   = '';
    protected array $attributes = [];
    protected array $style      = [];
    protected bool $render      = true;
    private ?string $cache      = null;
    private ?string $commentOut = null;
    private ?string $commentBefore = null;
    private ?string $commentAfter = null;

    public function addStyle(null|array|string $style): self
    {
        if (null === $style) {
            return $this;
        }
        if (is_string($style)) {
            $style = ['_' => $style];
        }
        $this->cache = null;
        $this->style = ArrayHelper::merge($this->style, $style);
        return $this;
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

    public function getAttribute(string $attr)
    {
        return $this->attributes[$attr] ?? null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttribute(string $attr, $value, bool $append = false): self
    {
        $this->cache = null;
        if (null === $value) {
            unset($this->attributes[$attr]);
        } elseif ($append && isset($this->attributes[$attr])) {
            $this->attributes[$attr] .= ' '.$value;
        } else {
            $this->attributes[$attr] = $value;
        }
        return $this;
    }

    public function setAttributes(array $attributes, bool $append = false): self
    {
        foreach ($attributes as $attr => $value) {
            $this->setAttribute($attr, $value, $append);
        }
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->cache   = null;
        $this->content = $content;
        return $this;
    }

    public function setTag(string $tag) : self
    {
        $this->tag = $tag;
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
        $this->purgeInvalidAttributes();
        $html = '';
        $i    = str_repeat(' ', $indentation * $level);
        if ($this->commentBefore) {
            $html.= $i.'<!-- '.$this->commentBefore.' -->'."\n";
        }
        if ('' === $this->tag) {
            $html .= $i.wordwrap($this->content, 80, $i."\n");
            $html .= "\n";
            return $html;
        }
        $html.= $i.'<'.$this->tag.$this->renderAttributes().'>';
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
            $html.= $i.'<!-- '.$this->commentAfter.' -->'."\n";
        }

        if ($this->commentOut) {
            $html = $i.'<!-- '.$this->commentOut."\n".$html.$i.'-->'."\n";
        }
        return $this->cache = $html;
    }

    protected function renderContent(int $indentation, int $level, string $content): string
    {
        $i = str_repeat(' ', $indentation * $level);
        return $i.wordwrap($content, 120, "\n".$i)."\n";
    }

    protected function renderAttributes(): string
    {
        if (count($this->style)) {
            $class = $this->getClasses();
            if (mb_strlen($class)) {
                $this->setAttribute('class', $class);
            }
        }
        $html = '';
        foreach ($this->attributes as $attr => $value) {
            if (is_bool($value) && !str_starts_with($attr, 'aria-')) {
                if ($value) {
                    $html .= ' '.$attr;
                } else {
                    //$html .= ' '.$attr.'="false"';
                }
            } else {
                if (is_bool($value) && $value) {
                    $value = 'true';
                } elseif (is_bool($value) && !$value) {
                    $value = 'false';
                } elseif (is_array($value)) {
                    $value = htmlentities(json_encode($value), ENT_QUOTES, 'UTF-8');
                }
                $html .= ' '.$attr.'="'.$value.'"';
            }
        }
        return $html;
    }

    public function getClasses(string $format = 'string'): string|array
    {
        $classes = [];
        foreach ($this->style as $attr => $class) {
            if ($attr[0] === '_') {
                continue;
            }
            if (is_string($class)) {
                $classes_ = explode(' ', trim($class));
                $classes  = array_merge($classes, $classes_);
            }
        }
        $classes = array_unique($classes);
        sort($classes);
        return 'string' === $format ? trim(implode(' ', $classes)) : $classes;
    }

    private function purgeInvalidAttributes(): void
    {
        $allowed = ['onclick'];
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
        foreach ($this->attributes as $attribute => $value) {
            if (!in_array($attribute, $allowed) && !str_starts_with($attribute, 'data-') && !str_starts_with($attribute, 'aria-')) {
                $purge[] = $attribute;
            }
        }
        foreach ($purge as $purgeAttribute) {
            unset($this->attributes[$purgeAttribute]);
        }
    }
}
