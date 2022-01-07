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
              table: 'elpriser',
            //   parsed: function (columns) {
            //     for (var row in columns) {
            //         for (var col in columns[row]) {
            //             var val = columns[row][col];
            //             if (null !== val && val.indexOf('€') !== -1) {
            //                 columns[row][col] = parseFloat(val.replace(',','.'))
            //             }
            //         }
            //     }
            //     console.log(columns)

            },
            chart: {
              type: 'column'
            }
          });</script>";
        $this->setContent($test);
    }
}
