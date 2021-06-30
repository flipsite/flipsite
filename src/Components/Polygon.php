<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Polygon extends AbstractComponent
{
    protected string $type = 'svg';

    public function build(array $data, array $style) : void
    {
        //$this->setContent($data['value'] ?? $data);
        $this->addStyle($style);
    }
}

// <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
//           <polygon points="50,0 100,0 50,100 0,100"></polygon>
//         </svg>
