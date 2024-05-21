<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Table extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnvironmentTrait;
    use Traits\NthTrait;
    protected string $tag = 'table';

    public function normalize(string|int|bool|array $data) : array
    {
        return $data;
    }

    public function build(array $data, array $style, array $options) : void
    {
        $this->addStyle($style);

        if ($data['th'] ?? false && count($data['th'])) {
            $tr      = new Element('tr');
            foreach ($data['th'] as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($style['th']);
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }

        if ($data['td'] ?? false && count($data['th'])) {
            foreach ($data['td'] as $i => $row) {
                $tr      = new Element('tr');
                foreach ($row as $cell) {
                    $td = new Element('td', true);
                    $td->addStyle($style['td']);
                    $td->setContent($cell);
                    $tr->addChild($td);
                }
                $this->addChild($tr);
            }
        }
    }
}
