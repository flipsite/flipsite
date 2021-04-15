<?php

declare(strict_types=1);

namespace Flipsite\Components;

interface ComponentListenerInterface
{
    public function handleComponentEvent(Event $event);
}
