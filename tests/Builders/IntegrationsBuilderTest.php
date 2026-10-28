<?php

use Flipsite\Builders\IntegrationsBuilder;
use Flipsite\Components\Document;
use Flipsite\Components\Element;
use PHPUnit\Framework\TestCase;

class IntegrationsBuilderTest extends TestCase
{
    private function render(bool $isLive, array $integrations): string
    {
        $document = new Document();
        $document->addChild(new Element('head'), 'head');
        $document->addChild(new Element('body'), 'body');
        $builder = new IntegrationsBuilder($isLive, $integrations);
        return $builder->getDocument($document)->render();
    }

    public function testPrivatusAnalyticsAddsScript()
    {
        $html = $this->render(true, ['privatusAnalytics' => 'pa_ABC123']);
        $this->assertStringContainsString('<script defer src="https://privatusstats.com/js/pa.js" data-site="pa_ABC123" data-modules="engage,auto,vitals,clicks,ask"></script>', $html);
        $this->assertStringNotContainsString('<!--', $html);
    }

    public function testPrivatusAnalyticsCommentedOutWhenNotLive()
    {
        $html = $this->render(false, ['privatusAnalytics' => 'pa_ABC123']);
        $this->assertStringContainsString('<!-- Not live environment', $html);
        $this->assertStringContainsString('data-site="pa_ABC123"', $html);
    }

    public function testPrivatusAnalyticsIgnoresInvalidSiteId()
    {
        $html = $this->render(true, ['privatusAnalytics' => 'pa_x"><script>alert(1)</script>']);
        $this->assertStringNotContainsString('privatusstats.com', $html);
        $html = $this->render(true, ['privatusAnalytics' => 'ABC123']);
        $this->assertStringNotContainsString('privatusstats.com', $html);
    }
}
