<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Number extends AbstractComponent
{
    use Traits\MarkdownTrait;
    use Traits\ClassesTrait;

    protected string $tag  = 'div';

    public function build(AbstractComponentData $component, InheritedComponentData $inherited): void
    {
        $value = (string)($data['value'] ?? 1);
        // If , as decimal separator, replace with .
        $value = str_replace(',', '.', $value);
        $value = str_replace(' ', '', $value);
        $value = floatval($value);

        // number_format(
        //     float $num,
        //     int $decimals = 0,
        //     ?string $decimal_separator = ".",
        //     ?string $thousands_separator = ","
        // ): string

        if (isset($data['format'])) {
            $value = $this->numberToFormat(intval($value), $data['format'] ?? '1');
        } else {
            $separators = [
                'none'      => '',
                'space'     => ' ',
                'comma'     => ',',
                'period'    => '.',
                'apostrophe' => '\'',
            ];
            $decimals           = intval($data['decimals'] ?? 0);
            $decimalSeparator   = $data['decimalSeparator'] ?? 'period';
            $thousandsSeparator = $data['thousandsSeparator'] ?? 'none';
            $initial            = $value;
            $value              = number_format($value, $decimals, $separators[$decimalSeparator], $separators[$thousandsSeparator]);
            if (floor($initial) != $initial && ($data['removeZeros'] ?? false)) {
                while (substr($value, -1) === '0' || substr($value, -1) === $separators[$decimalSeparator]) {
                    // Remove the last character from the string
                    $value = substr($value, 0, -1);
                }
            }
        }
        $this->addStyle($style);
        if (isset($data['content'])) {
            $content = str_replace('[number]', (string)$value, $data['content']);
            $content = $this->getMarkdownLine($content, [], $options['appearance']);
            $content = $this->addClassesToHtml($content, ['strong'], $style, $options['appearance']);
            $this->setContent($content);
        } else {
            $this->setContent($value);
        }
    }

    private function numberToFormat(int $number, string $format = '1'): string
    {
        if ($number < 1 || $number > 26) {
            return (string)$number;
        }
        if ($format === '1.') {
            return $number.'.';
        }

        $lowercaseLetters = range('a', 'z');
        $uppercaseLetters = range('A', 'Z');
        $romanNumerals    = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii', 'xiii', 'xiv', 'xv', 'xvi', 'xvii', 'xviii', 'xix', 'xx', 'xxi', 'xxii', 'xxiii', 'xxiv', 'xxv', 'xxvi'];

        switch ($format) {
            case 'a':
                return $lowercaseLetters[$number - 1];
            case 'A':
                return $uppercaseLetters[$number - 1];
            case 'i':
                return $romanNumerals[$number - 1];
            case 'I':
                return strtoupper($romanNumerals[$number - 1]);
        }
        return (string)$number;
    }
}
