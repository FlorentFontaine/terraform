<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class DossierRepository
{
    /**
     * Récupère les balances importées d'un dossier
     * @return array
     */
    public function getBalanceImportByDossier(): array
    {
        if(!$_SESSION["station_DOS_NUM"]) {
            return [];
        }
        return (new QueryBuilder())
            ->from("balanceimport")
            ->where("DOS_NUM = :DOS_NUM")
            ->setParam("DOS_NUM", $_SESSION["station_DOS_NUM"])
            ->orderBy("BALI_MOIS", "DESC")
            ->getAll();
    }
}
