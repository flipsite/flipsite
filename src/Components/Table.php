<?php

declare(strict_types=1);

namespace Flipsite\Components;

use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnviromentTrait;
    protected string $type = 'table';

    public function build(array $data, array $style) : void
    {
        $this->addStyle($style['container'] ?? []);
        $header = $data['header'] ?? null;
        if (is_string($header)) {
            $header = explode(',', $header);
        }
        $records = $data['rows'] ?? [];
        if (isset($data['import']) && mb_strpos($data['import'], '.csv')) {
            $filename = $this->enviroment->getSiteDir().'/'.$data['import'];
            if (file_exists($filename)) {
                $csv = Reader::createFromPath($filename, 'r');
                if (null === $header) {
                    $csv->setHeaderOffset(0);
                    $header = $csv->getHeader();
                }
                $records = $csv->getRecords();
            }
        }
        if (null !== $header) {
            $tr = new Element('tr');
            foreach ($header as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($style['thAll'] ?? []);
                $th->addStyle($style['th'][$i] ?? []);
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }
        $row = 0;
        foreach ($records as $record) {
            $tr = new Element('tr');
            $tr->addStyle($style['trAll'] ?? []);
            if (0 === $row % 2) {
                $tr->addStyle($style['trEven'] ?? []);
            } else {
                $tr->addStyle($style['trOdd'] ?? []);
            }
            ++$row;
            $i = 0;
            foreach ($record as $col) {
                $td = new Element('td', true);
                $td->addStyle($style['tdAll'] ?? []);
                $td->addStyle($style['td'][$i++] ?? []);
                $td->setContent($col);
                $tr->addChild($td);
            }
            $this->addChild($tr);
        }
    }
}

// //load the CSV document from a file path
// $csv = Reader::createFromPath('/path/to/your/csv/file.csv', 'r');
// $csv->setHeaderOffset(0);

// $header  = $csv->getHeader(); //returns the CSV header record
// $records = $csv->getRecords(); //returns all the CSV records as an Iterator object

// echo $csv->toString(); //returns the CSV document as a string
