<?php

namespace Facades\Modules;

use Facades\Facade;
use Services\ModuleService;

class Module extends Facade
{
    public static function definition(): string
    {
        return ModuleService::class;
    }
}
