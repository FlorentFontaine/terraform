<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class ModuleRepository
{
    /**
     * Récupère les modules actifs
     * @return array
     */
    public function getAllActiveModule(): array
    {
        return (new QueryBuilder())
            ->from("modules")
            ->where("MOD_ENABLE = :MOD_ENABLE")
            ->setParam("MOD_ENABLE", true)
            ->getAll();
    }
}
