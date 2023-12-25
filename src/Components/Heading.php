<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Heading extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\GlobalVarsTrait;
    protected bool $oneline = true;
    protected string $tag   = 'h2';

    public function build(array $data, array $style, array $options): void
    {
        if (isset($style['highlight'])) {
            $data = $this->handleHighlight($data, $style['highlight'], $options['appearance']);
        }
        $this->addStyle($style);
        if (isset($data['name'])) {
            $a = new Element('a');
            $a->setContent($data['value']);
            $a->setAttribute('name', $data['name']);
            $this->addChild($a);
        } else {
            $this->setContent($data['value']);
        }
    }

    public function normalize(string|int|bool|array $data): array
    {
        if (!is_array($data)) {
            return ['value' => (string)$data];
        }
        return $data;
    }

    private function handleHighlight(array $data, array $style, string $appearance): array
    {
        $pattern = '/\[([^\[\]]+)\]/';
        preg_match_all($pattern, $data['value'], $matches);

        foreach ($matches[1] as $highlightString) {
            $match = '[us]';
            $highlightData = $data['highlight'] ?? [];
            $highlightData['value'] = $highlightString;
            $style['tag'] = 'span';
            $span = $this->builder->build('span', $highlightData, $style, ['appearance' => $appearance]);
            $span->oneline = true;
            $data['value'] = str_replace('[' . $highlightString . ']', trim($span->render()), $data['value']);
        }
        unset($data['highlight']);
        return $data;
    }
}
