<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class BalanceRepository
{
    public function getResultatsCompte(int $codeCompte = null): array
    {

        $query = (new QueryBuilder())
            ->select("codeCompte, SUM(BAL_CUMUL) AS BAL_CUMUL, SUM(BAL_BALANCE) AS BAL_BALANCE, BAL_NUM")
            ->from("balance")
            ->index("comptes.code_compte")
            ->join("comptes", "comptes.code_compte = balance.codeCompte")
            ->join("dossier", "dossier.DOS_NUM = balance.DOS_NUM")
            ->join("station", "station.STA_NUM = dossier.STA_NUM")
            ->where("codeStation = :codeStation")
            ->where("BAL_MOIS = :BAL_MOIS")
            ->where("dossier.DOS_NUM = :DOS_NUM")
            ->setParam("codeStation", $_SESSION["station_STA_NUM"])
            ->setParam("BAL_MOIS", $_SESSION["MoisHisto"])
            ->setParam("DOS_NUM", $_SESSION["station_DOS_NUM"])
            ->groupBy("codeCompte");
            
        if ($codeCompte) {
            $query->where("codeCompte = :codeCompte")
                ->setParam("codeCompte", $codeCompte);

            return $query->get() ?? [];
        }

        return $query->getAll();
    }

    public function getEcartReelPrevu($codePoste = null): array
    {

        $queryPrevu = (new QueryBuilder())
            ->select("resultatposte.codePoste, sum(Montant) as Montant, Famille, SsFamille, 'prevu', compteposte.Type, SUM(resultatposte.PrevTauxMontant) as PrevTauxMontant ")
            ->from("resultatposte")
            ->join("compteposte", "compteposte.codePoste = resultatposte.codePoste")
            ->where("Periode BETWEEN '" . date('Y-m-00', strtotime($_SESSION["station_DOS_DEBEX"])) . "' AND '" . $_SESSION["MoisHisto"] . "'")
            ->where("DOS_NUM = :DOS_NUM")
            ->where("resultat = 1")
            ->where("marge = 0")
            ->where("prevagip = 0")
            ->groupBy("resultatposte.codePoste");


        $queryPrevuMarge = (new QueryBuilder())
            ->select("resultatposte.codePoste, sum(Montant) as Montant, Famille, SsFamille, 'marge', compteposte.Type, SUM(resultatposte.PrevTauxMontant) as PrevTauxMontant ")
            ->from("resultatposte")
            ->join("compteposte", "compteposte.codePoste = resultatposte.codePoste")
            ->where("Periode BETWEEN '" . date('Y-m-00', strtotime($_SESSION["station_DOS_DEBEX"])) . "' AND '" . $_SESSION["MoisHisto"] . "'")
            ->where("DOS_NUM = :DOS_NUM")
            ->where("resultat = 1")
            ->where("marge = 1")
            ->where("prevagip = 0")
            ->groupBy("resultatposte.codePoste");

        if($codePoste) {
            if(is_array($codePoste)) {
                $queryPrevu->where("resultatposte.codePoste IN (" . implode(",", $codePoste) . ")");
                $queryPrevuMarge->where("resultatposte.codePoste IN (" . implode(",", $codePoste) . ")");
            } else {
                $queryPrevu->where("resultatposte.codePoste = :codePoste")
                    ->setParam("codePoste", $codePoste);
                $queryPrevuMarge->where("resultatposte.codePoste = :codePoste")
                    ->setParam("codePoste", $codePoste);
            }
        }

        $queryReal = (new QueryBuilder())
            ->select("compteposte.codePoste, sum(BAL_CUMUL) as Montant, Famille, SsFamille, 'realise', compteposte.Type, 0 as PrevTauxMontant")
            ->from("balance")
            ->join("comptes", "comptes.code_compte = balance.codeCompte")
            ->join("compteposte", "compteposte.codePoste = comptes.codePoste")
            ->join("dossier", "dossier.DOS_NUM = balance.DOS_NUM")
            ->join("station", "station.STA_NUM = dossier.STA_NUM")
            ->where("BAL_MOIS = :mois")
            ->where("codeStation = :codeStation")
            ->where("dossier.DOS_NUM = :DOS_NUM")
            ->setParam("mois", $_SESSION["MoisHisto"])
            ->setParam("codeStation", $_SESSION["station_STA_NUM"])
            ->setParam("DOS_NUM", $_SESSION["station_DOS_NUM"])
            ->groupBy("compteposte.codePoste")
            ->union($queryPrevu)
            ->union($queryPrevuMarge);

        if($codePoste) {
            if(is_array($codePoste)) {
                $queryReal->where("compteposte.codePoste IN (" . implode(",", $codePoste) . ")");
            } else {
                $queryReal->where("compteposte.codePoste = :codePoste")
                    ->setParam("codePoste", $codePoste);
            }
        }

        return $queryReal->getAll();
    }
}
