<?php

namespace Services;

use Repositories\ModuleRepository;

class ModuleService extends AbstractService
{
    /**
     * Mettre ici en constante chaque clé de module
     */
    public const COMMENTAIRE = "commentaire";

    private ModuleRepository $moduleRepository;

    public function __construct(ModuleRepository $moduleRepository) {
        $this->moduleRepository = $moduleRepository;

        // TODO Remove this line when the full app will be under twig
        $this->loadModules();
    }

    public function getAllModulesEnable(): array
    {
        return $this->moduleRepository->getAllActiveModule();
    }

    public function isModuleEnable(string $key): bool
    {
        return $this->session->get(['MODULES', $key]) === true;
    }

    // TODO This function is just to properly load the module because it's usually loaded by kernel (not used in old way)
    public function loadModules()
    {
        $modules = $this->getAllModulesEnable();

        // Reset des modules avant redéfinition
        $_SESSION['MODULES'] = [];

        foreach ($modules as $module) {
            $_SESSION['MODULES'][$module['MOD_KEY']] = true;
        }
    }
}
