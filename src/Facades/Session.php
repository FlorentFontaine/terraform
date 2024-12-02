<?php

namespace Facades;

use Classes\SessionHandler;

class Session extends Facade
{
    public static function definition(): string
    {
        return SessionHandler::class;
    }
}
