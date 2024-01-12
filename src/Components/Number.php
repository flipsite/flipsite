<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Number extends AbstractComponent
{
    protected string $tag  = 'div';

    public function build(array $data, array $style, array $options) : void
    {
        $value = $this->numberToFormat(intval($data['value'] ?? 1), $data['format'] ?? '1');
        $this->addStyle($style);
        if (isset($data['content'])) {
            $this->setContent(str_replace('[number]', $value, $data['content']));
        } else {
            $this->setContent($value);
        }
    }

    private function numberToFormat(int $number, string $format = '1') : string
    {
        if ($number < 1 || $number > 26) {
            return (string)$number;
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
