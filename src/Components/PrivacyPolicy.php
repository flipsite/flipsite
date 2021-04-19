<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class PrivacyPolicy extends AbstractComponent
{
    use Traits\MarkdownTrait;
    protected string $type = 'article';

    public function build(array $data, array $style, array $flags) : void
    {
        $this->addStyle($style['container'] ?? []);
        $filter = new \Twig\TwigFilter('s', function (string $string) : string {
            $last = mb_substr($string, -1);
            return 's' === $last ? $string.'`' : $string.'`s';
        });
        $filter2 = new \Twig\TwigFilter('tel', function (string $string) : string {
            return $string;
        });
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__);
        $twig   = new \Twig\Environment($loader);
        $twig->addFilter($filter);
        $twig->addFilter($filter2);
        $privacyPolicy = $twig->render('privacy-policy.twig.md', $data);
        $this->content = $this->getMarkdown($privacyPolicy, $style ?? null);
    }
}
