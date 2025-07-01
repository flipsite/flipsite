<?php

declare(strict_types=1);
namespace Flipsite\Components;

use Flipsite\Data\AbstractComponentData;
use Flipsite\Data\InheritedComponentData;

final class Text extends AbstractComponent
{
    use Traits\DateFilterTrait;
    use Traits\PhoneFilterTrait;
    use Traits\UrlFilterTrait;
    use Traits\CheckTextTrait;

    protected string $tag   = '';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $data          = $component->getData();
        $this->content = $data['value'] ?? $data['fallback'] ?? '';
        $this->content = $this->checkText($this->content, 'Plain text');
        if (!$this->content) {
            $this->render = false;
            return;
        }
        if (isset($data['formatDate'])) {
            $this->content = $this->parseDate($this->content, $data['formatDate']);
        }
        if (isset($data['formatPhone'])) {
            $this->content = $this->parsePhone($this->content, $data['formatPhone']);
        }
        if (isset($data['formatUrl'])) {
            $this->content = $this->parseUrl($this->content, $data['formatUrl']);
        }
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false): string
    {
        if ($this->tag === '') {
            $i    = str_repeat(' ', $indentation * $level);
            $html = $i;
            $html .= $this->content;
            if (!$this->oneline && !$oneline) {
                $html .= "\n";
            }
            return $html;
        } else {
            return parent::render($indentation, $level, $oneline) ?? '';
        }
    }
}
