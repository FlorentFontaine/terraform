<?php

namespace Session;

use Classes\SessionHandler;
use Services\ModuleService;

/**
 * Class ModuleLoader
 *
 * The ModuleLoader class is responsible for loading and enabling modules in the application.
 * It retrieves all the enabled modules from the ModuleService and sets them as enabled in the session.
 */
class Module
{
    private ModuleService $service;

    private SessionHandler $session;

    public function __construct(SessionHandler $session, ModuleService $service) {
        $this->service = $service;
        $this->session = $session;
    }

    public function run() {
        $modules = $this->service->getAllModulesEnable();

        // Reset des modules avant redéfinition
        $this->session->remove(['MODULES']);

        foreach ($modules as $module) {
            $this->session->set(['MODULES', $module['MOD_KEY']], true);
        }
    }
}
