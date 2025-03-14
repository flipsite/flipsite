<?php

declare(strict_types=1);

namespace Flipsite\Components\Traits;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\InlinesOnly\InlinesOnlyExtension;
use Flipsite\Utils\StyleAppearanceHelper;

trait MarkdownTrait
{
    use ActionTrait;

    private function getMarkdownLine(string $markdown, array $tags, array $style, string $appearance): string
    {
        $markdown = $this->emailsToLinks($markdown);
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new InlinesOnlyExtension());
        $environment->addExtension(new AttributesExtension());

        $converter = new CommonMarkConverter([], $environment);
        $html = $converter->convert($markdown);
        $html = $this->fixUrlsInHtml($html);
        return $html;
    }

    private function emailsToLinks(string $markdown): string
    {
        return preg_replace(
            '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
            '[$1](mailto:$1)',
            $markdown
        );
    }
}
