<?php

declare(strict_types=1);
namespace Flipsite\Components\Traits;

trait GlobalVarsTrait
{
    use BuilderTrait;

    private function checkGlobalVars(?string $content) : ?string
    {
        if (!$content) {
            return null;
        }

        if (strpos($content, '{copyright.year}') !== false) {
            $this->builder->dispatch(new \Flipsite\Builders\Event('ready-script', 'copyright', file_get_contents(__DIR__.'/../../../js/ready.copyright.min.js')));
            $content = str_replace('{copyright.year}', '<span data-copyright>'.date('Y').'</span>', $content);
        }

        return $content;
    }
}
