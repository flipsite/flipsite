<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Highchart extends AbstractComponent
{
    protected string $tag   = 'figure';

    public function build(array $data, array $style, string $appearance) : void
    {
        $test = '<div id="aaa"></div><script src="https://code.highcharts.com/highcharts.js"></script>
        <script src="https://code.highcharts.com/modules/data.js"></script>
        <script src="https://code.highcharts.com/modules/exporting.js"></script>
        <script src="https://code.highcharts.com/modules/accessibility.js"></script>';
        $test .= "<script>Highcharts.chart('aaa', {
            data: {
              table: 'elpriser'
            },
            chart: {
              type: 'column'
            },
            title: {
              text: 'Data extracted from a HTML table in the page'
            },
            yAxis: {
              allowDecimals: false,
              title: {
                text: 'Units'
              }
            },
          });</script>";
        $this->setContent($test);
    }
}
