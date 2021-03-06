<?php

namespace Project;

use Perfumer\Framework\Gateway\CompositeGateway;

class Gateway extends CompositeGateway
{
    protected function configure(): void
    {
        $this->addModule('feed', null,   null, 'http');
        $this->addModule('feed', 'feed', null, 'cli');
    }
}