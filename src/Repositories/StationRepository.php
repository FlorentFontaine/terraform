<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class StationRepository
{
    /**
     * Récupère les exercices d'une station
     * @return array
     */
    public function getAllExerciceByStation(): array
    {
        if(!$_SESSION["station_STA_NUM"]) {
            return [];
        }
        return (new QueryBuilder())
            ->from("dossier")
            ->where("STA_NUM = :STA_NUM")
            ->setParam("STA_NUM", $_SESSION["station_STA_NUM"])
            ->orderBy("DOS_NUM", "DESC")
            ->getAll();
    }

    /**
     * Récupère le cabinet comptable d'une station
     * @return array
     */
    public function getCabinetComptableByStation(): array
    {
        if(!$_SESSION["station_STA_NUM"]) {
            return [];
        }
        return (new QueryBuilder())
            ->from("stationcc")
            ->where("STA_NUM = :STA_NUM")
            ->leftJoin("comptable", "stationcc.CC_NUM = comptable.CC_NUM")
            ->leftJoin("cabinet", "comptable.CAB_NUM = cabinet.CAB_NUM")
            ->setParam("STA_NUM", $_SESSION["station_STA_NUM"])
            ->get();
    }

    /**
     * Récupère le cabinet juridique d'une station
     * @return array
     */
    public function getCabinetJuridiqueByStation(): array
    {
        if(!$_SESSION["station_STA_NUM"]) {
            return [];
        }
        // Les cabinet juridique n'existe pas encore dans my report
        return (new QueryBuilder())
            ->from("cabinet_juridique")
            ->where("STA_NUM = :STA_NUM")
            ->setParam("STA_NUM", $_SESSION["station_STA_NUM"])
            ->get();
    }
}
