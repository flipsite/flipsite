<?php

declare(strict_types=1);
namespace Flipsite\Components;

use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnvironmentTrait;
    use Traits\NthTrait;
    protected string $tag = 'table';

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            $data = ['import' => $data];
        }
        if (isset($data['import'])) {
            $filename = $this->environment->getSiteDir().'/'.$data['import'];
            unset($data['import']);
            if (file_exists($filename)) {
                $csv = Reader::createFromPath($filename, 'r');
                $csv->setDelimiter(';');
                if (!isset($data['header'])) {
                    $csv->setHeaderOffset(0);
                    $data['header'] = [];
                    foreach ($csv->getHeader() as $h) {
                        $data['header'][] = $h;
                    }
                }
                $data['rows'] = [];
                foreach ($csv->getRecords() as $i => $row) {
                    $data['rows'][$i] = [];
                    foreach ($row as $col) {
                        $data['rows'][$i][] = $col;
                    }
                }
            }
        }
        if (isset($data['header']) && is_string($data['header'])) {
            $data['header'] = explode(',', $data['header']);
        }
        return $data;
    }

    public function build(array $data, array $style, string $appearance) : void
    {
        $this->addStyle($style);
        if ($data['header'] ?? false) {
            $tr      = new Element('tr');
            foreach ($data['header'] as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($this->getNth($i, count($data['header']), $style['th'] ?? [], 'th'));
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }

        $totalRows = count($data['rows']);
        foreach ($data['rows'] as $i => $row) {
            $tr = new Element('tr');
            $tr->addStyle($this->getNth($i, $totalRows, $style['tr'] ?? [], 'tr'));
            foreach ($row as $j => $col) {
                $tag = $style['td'][$j]['tag'] ?? 'td';
                $td  = new Element($tag, true);
                $td->addStyle($this->getNth($j, $totalRows, $style['td'] ?? [], 'td'));

                if (isset($data['format'])) {
                    $td->setContent($this->format($data['format'], $col, $j));
                } else {
                    $td->setContent((string)$col);
                }
                $tr->addChild($td);
            }
            $this->addChild($tr);
        }
    }

    private function format(array $format, string $content, int $index) : string
    {
        if (!$content) {
            return $content;
        }
        $f = $format['tdAll'] ?? 'none';
        if (isset($format['td'][$index])) {
            $f = $format['td'][$index];
        }
        $parts = explode('|', $f);
        switch ($parts[0]) {
            case 'currency':
                $value  = number_format(floatVal($content), 2, ',', '');
                $format = $parts[1] ?? false;
                if (isset($parts[1])) {
                    return  sprintf($parts[1], $value);
                }
                // no break
            case 'none':
            default: return $content;
        }
    }
}
