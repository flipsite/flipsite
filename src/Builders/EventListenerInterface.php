<?php

declare(strict_types=1);

namespace Flipsite\Builders;

interface EventListenerInterface
{
    public function handleEvent(Event $event);
}
