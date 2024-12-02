<?php

namespace Services;

use Classes\SessionHandler;

abstract class AbstractService
{
    protected ?SessionHandler $session = null;

    public function setSession(SessionHandler $session): void
    {
        $this->session = $session;
    }
}
