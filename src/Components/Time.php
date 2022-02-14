<?php

declare(strict_types=1);
namespace Flipsite\Components;

final class Time extends AbstractComponent
{
    use Traits\BuilderTrait;
    use Traits\MarkdownTrait;
    use Traits\PathTrait;

    protected bool $oneline = true;
    protected string $tag   = 'time';

    public function build(array $data, array $style, string $appearance) : void
    {
        if (isset($data['timezone'])) {
            date_default_timezone_set($data['timezone']);
        }
        setlocale(LC_TIME, $this->path->getLanguage()->getLocale());
        $this->addStyle($style);
        $this->setContent(strftime($data['format'], $data['timestamp']));
    }

    public function normalize(string|int|bool|array $data) : array
    {
        $data['timestamp'] = strtotime($data['value']);
        return $data;
    }
}
