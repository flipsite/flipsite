<?php

declare(strict_types=1);

namespace Flipsite\Data;

use Symfony\Component\Yaml\Yaml;

final class MarkdownParser
{
    /**
     * @var array<string,mixed>
     */
    private array $yaml = [];
    private string $markdown;

    public function __construct(string $data)
    {
        $content   = $data;
        $delimiter = "---\n";
        $length    = mb_strlen($delimiter);
        if (mb_substr($content, 0, $length) === $delimiter) {
            $content = mb_substr($content, $length);
            $pos     = mb_strpos($content, $delimiter);
            if (false !== $pos) {
                $this->yaml = Yaml::parse(trim(mb_substr($content, 0, $pos)));
                $content    = mb_substr($content, $pos + $length);
            }
        }
        $this->markdown = trim($content);
    }

    public function getMarkdown(bool $parsed = false) : ?string
    {
        if (0 === mb_strlen($this->markdown)) {
            return null;
        }
        if (!$parsed) {
            return $this->markdown;
        }
        $parsedown = new \Parsedown();
        return $parsedown->text($this->markdown);
    }

    public function getYaml() : array
    {
        return $this->yaml;
    }
}
