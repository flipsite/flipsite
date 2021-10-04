<?php

declare(strict_types=1);
namespace Flipsite\Components;

use League\Csv\Reader;

final class Table extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\EnviromentTrait;
    protected string $tag = 'table';

    public function with(ComponentData $data) : void
    {
        $this->addStyle($data->getStyle('container'));
        $header = $data->get('header') ?? null;
        if (is_string($header)) {
            $header = explode(',', $header);
        }
        $records = $data->get('rows') ?? [];
        $import  = $data->get('import') ?? null;
        if ($import && mb_strpos($import, '.csv')) {
            $filename = $this->enviroment->getSiteDir().'/'.$import;
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
            $tr      = new Element('tr');
            $thStyle = $data->getStyle('th') ?? [];
            foreach ($header as $i => $col) {
                $th = new Element('th', true);
                $th->addStyle($data->getStyle('thAll') ?? []);
                $th->addStyle($thStyle[$i] ?? null);
                $th->setContent($col);
                $tr->addChild($th);
            }
            $this->addChild($tr);
        }
        $row     = 0;
        $tdStyle = $data->getStyle('td') ?? [];
        foreach ($records as $record) {
            $tr = new Element('tr');
            $tr->addStyle($data->getStyle('trAll'));
            if (0 === $row % 2) {
                $tr->addStyle($data->getStyle('trEven'));
            } else {
                $tr->addStyle($data->getStyle('trOdd'));
            }
            ++$row;
            $i = 0;
            foreach ($record as $col) {
                $td = new Element('td', true);
                $td->addStyle($data->getStyle('tdAll'));
                $td->addStyle($tdStyle[$i++] ?? []);
                $td->setContent($col);
                $tr->addChild($td);
            }
            $this->addChild($tr);
        }
    }
}
