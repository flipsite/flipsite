<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait MarkdownTrait
{
    use ActionTrait;

    private function getMarkdownLine(string $text): string
    {
        $text      = strip_tags($text, '<br><span>');
        $parsedown = new \Parsedown();
        $text      = str_replace("\n", ' ', $text);
        $text      = trim($text);
        $html      = $parsedown->line($text);
        $html      = $this->fixUrlsInHtml($html);
        return $html;
    }
}
