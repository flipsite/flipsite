<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Date extends AbstractComponent
{
    use Traits\PathTrait;
    protected string $tag  = 'time';

    public function build(array $data, array $style, array $options): void
    {
        $this->addStyle($style);
        $timestamp = isset($data['value']) ? strtotime($data['value']) : time();
        $format = \IntlDateFormatter::FULL;
        switch ($data['format'] ?? '') {
            case 'full': $format = \IntlDateFormatter::FULL;
                break;
            case 'long': $format = \IntlDateFormatter::LONG;
                break;
            case 'medium': $format = \IntlDateFormatter::MEDIUM;
                break;
            case 'short': $format = \IntlDateFormatter::SHORT;
                break;
            case 'none': $format = \IntlDateFormatter::NONE;
                break;

        }
        $dateFormatter = new \IntlDateFormatter(
            (string)$this->path->getLanguage(),
            $format,    // Date format (can be SHORT, MEDIUM, LONG, FULL)
            \IntlDateFormatter::NONE,     // Time format (NONE means no time, or you can set it)
            null,
            null,
            $data['pattern'] ?? null
        );
        $date = $dateFormatter->format($timestamp) ?? 'Invalid date';
        if (isset($data['content'])) {
            $this->setContent(str_replace('[date]', $date, $data['content']));
        } else {
            $this->setContent($date);
        }
    }
}
