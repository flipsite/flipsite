<?php

declare(strict_types=1);

namespace Flipsite\Builders;

use Flipsite\Components\Document;

interface BuilderInterface
{
    public function getDocument(Document $document) : Document;
}
