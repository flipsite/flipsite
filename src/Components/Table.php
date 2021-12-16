<?php

declare(strict_types=1);
namespace Flipsite\Components;

use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnviromentTrait;
    protected string $tag = 'table';

    public function normalize(string|int|bool|array $data) : array
    {
        if (is_string($data)) {
            $data = ['import' => $data];
        }
        if (isset($data['import'])) {
            $filename = $this->enviroment->getSiteDir().'/'.$data['import'];
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
        if (null !== $data['header']) {
            $tr      = new Element('tr');
            foreach ($data['header'] as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($style['thAll'] ?? null);
                $th->addStyle($style['th'][$i] ?? null);
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }

        foreach ($data['rows'] as $i => $row) {
            $tr = new Element('tr');
            $tr->addStyle($style['trAll'] ?? null);
            if (0 === $i % 2) {
                $tr->addStyle($style['trEven'] ?? null);
            } else {
                $tr->addStyle($style['trOdd'] ?? null);
            }
            $tr->addStyle($style['tr'][$i++] ?? []);
            foreach ($row as $j => $col) {
                $tag = $style['td'][$j]['tag'] ?? 'td';
                $td  = new Element($tag, true);
                $td->addStyle($style['tdAll'] ?? []);
                $td->addStyle($style['td'][$j] ?? []);
                if (isset($data['format'])) {
                    $td->setContent($this->format($data['format'], $col, $j));
                } else {
                    $td->setContent($col);
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
            // $tdStyle = $data->getStyle('td') ?? [];
            // foreach ($records as $record) {
            //     $tr = new Element('tr');
            //     $tr->addStyle($data->getStyle('trAll'));
            //     if (0 === $row % 2) {
            //         $tr->addStyle($data->getStyle('trEven'));
            //     } else {
            //         $tr->addStyle($data->getStyle('trOdd'));
            //     }
            //     ++$row;
            //     $i = 0;
            //     foreach ($record as $col) {
            //         $td = new Element('td', true);
            //         $td->addStyle($data->getStyle('tdAll'));
            //         $td->addStyle($tdStyle[$i++] ?? []);
            //         $td->setContent($col);
            //         $tr->addChild($td);
            //     }
            //     $this->addChild($tr);
            // }

        // $this->addStyle($data->getStyle('container'));
        // $header = $data->get('header') ?? null;
        // if (is_string($header)) {
        //     $header = explode(',', $header);
        // }
        // $records = $data->get('rows') ?? [];
        // $import  = $data->get('import') ?? null;
        // if ($import && mb_strpos($import, '.csv')) {
        //     $filename = $this->enviroment->getSiteDir().'/'.$import;
        //     if (file_exists($filename)) {
        //         $csv = Reader::createFromPath($filename, 'r');
        //         if (null === $header) {
        //             $csv->setHeaderOffset(0);
        //             $header = $csv->getHeader();
        //         }
        //         $records = $csv->getRecords();
        //     }
        // }
        // if (null !== $header) {
        //     $tr      = new Element('tr');
        //     $thStyle = $data->getStyle('th') ?? [];
        //     foreach ($header as $i => $col) {
        //         $th = new Element('th', true);
        //         $th->addStyle($data->getStyle('thAll') ?? []);
        //         $th->addStyle($thStyle[$i] ?? null);
        //         $th->setContent($col);
        //         $tr->addChild($th);
        //     }
        //     $this->addChild($tr);
        // }
        // $row     = 0;
        // $tdStyle = $data->getStyle('td') ?? [];
        // foreach ($records as $record) {
        //     $tr = new Element('tr');
        //     $tr->addStyle($data->getStyle('trAll'));
        //     if (0 === $row % 2) {
        //         $tr->addStyle($data->getStyle('trEven'));
        //     } else {
        //         $tr->addStyle($data->getStyle('trOdd'));
        //     }
        //     ++$row;
        //     $i = 0;
        //     foreach ($record as $col) {
        //         $td = new Element('td', true);
        //         $td->addStyle($data->getStyle('tdAll'));
        //         $td->addStyle($tdStyle[$i++] ?? []);
        //         $td->setContent($col);
        //         $tr->addChild($td);
        //     }
        //     $this->addChild($tr);
        // }
