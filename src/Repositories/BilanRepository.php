<?php

namespace Repositories;

use Classes\DB\QueryBuilder;
use Helpers\StringHelper;

class BilanRepository
{
    /**
     * Récupère tous les postes du bilan par type
     *
     * @param string $type
     * @return array
     */
    public function getAllPostesByType(string $type): array
    {
        $query = (new QueryBuilder())
            ->select('comptebilan.*, SUM(comptes.CPT_VISIBLE) as visible')
            ->from('comptebilan')
            ->leftjoin("AS_comptes_comptepostebilan", "AS_comptes_comptepostebilan.CPTB_NUM = comptebilan.CPTB_NUM")
            ->leftjoin("comptes", "comptes.code_compte = AS_comptes_comptepostebilan.codeCompte")
            ->where("CPTB_TYPE = :CPTB_TYPE")
            ->setParam("CPTB_TYPE", $type)
            ->groupBy("comptebilan.CPTB_NUM")
            ->having("visible > 0")
            ->orderBy("CPTB_ORDRE")
            ->index("CPTB_NUM");

        return $query->getAll();
    }

    public function getPostesBilan($cptbNum = null): array
    {
        $query = (new QueryBuilder())
            ->select('comptebilan.*')
            ->from('comptebilan');

        if ($cptbNum) {
            if(is_array($cptbNum)){
                $query->where("comptebilan.CPTB_NUM IN (" . implode(",", $cptbNum) . ")");
            } else {
                $query->where("comptebilan.CPTB_NUM = :CPTB_NUM")
                ->setParam("CPTB_NUM", $cptbNum);
            }
        }

        $query->orderBy("CPTB_ORDRE")
        ->index("CPTB_NUM");

        return $query->get();
    }

    public function getPostesBilanBySlug($slug = null): array
    {
        $query = (new QueryBuilder())
            ->select('comptebilan.*')
            ->from('comptebilan');

        if(is_array($slug)){
            $quotedArray = array_map(function($item) {
                return '"' . addslashes(utf8_decode($item)) . '"';
            }, $slug);
            $query->where('comptebilan.CPTB_SFAMILLE IN (' . implode(",", $quotedArray) . ')');
        } else {
            $query
            ->where('comptebilan.CPTB_SFAMILLE = "' . addslashes(utf8_decode($slug)) . '"');
        }

        $query
            ->orderBy("CPTB_ORDRE")
            ->index("CPTB_NUM");

        return $query->getAll();
    }

    public function getComptesBilan($cptbNum = null, $type = null)
    {

        $query = (new QueryBuilder())
            ->select('comptebilan.*, comptes.*')
            ->index("comptes.code_compte")
            ->from('comptebilan')
            ->join("AS_comptes_comptepostebilan", "AS_comptes_comptepostebilan.CPTB_NUM = comptebilan.CPTB_NUM");
            if($type == "Stock"){
                $query->join("comptes", "comptes.CodeCompteAttache = AS_comptes_comptepostebilan.codeCompte");
            } else {
                $query->join("comptes", "comptes.code_compte = AS_comptes_comptepostebilan.codeCompte");
            }
        
        $query->orderBy("comptes.numero");

        if ($cptbNum) {
            $query->where("comptebilan.CPTB_NUM = :CPTB_NUM")
            ->setParam("CPTB_NUM", $cptbNum);
        }

        return $query->getAll();
    }

    /**
     * Récupère le résultat d'un dossier
     *
     * @param string $dosNum
     * @param string|null $periode
     * @return array|string|null
     */
    public function getResultat(string $dosNum, string $periode = null)
    {
        $query = (new QueryBuilder())
            ->select("sum(BALI_RES) as BALI_RES, sum(BALI_RESPREV) as BALI_RESPREV, sum(BALI_RESPREVAGIP) as BALI_RESPREVAGIP")
            ->from("balanceimport")
            ->where("DOS_NUM = :DOS_NUM")
            ->setParam("DOS_NUM", $dosNum);

        if ($periode) {
            $query->where("BALI_MOIS <= :BALI_MOIS")
                ->setParam("BALI_MOIS", $periode);
        }

        return $query->get();
    }

    /**
     * Récupère le résultat par poste d'un dossier
     *
     * @param string $dosNum
     * @param string $mois
     * @param string $type
     * @param int|null $cptbNum
     * @return array
     */
    public function getPosteResultat(string $dosNum, string $mois, string $type, int $cptbNum = null): array
    {
        $query = (new QueryBuilder())
            ->select('AS_comptes_comptepostebilan.sens')
            ->from("balance")
            ->join("AS_comptes_comptepostebilan", "AS_comptes_comptepostebilan.codeCompte = Balance.codeCompte")
            ->join("comptes", "comptes.code_compte = Balance.codeCompte")
            ->where("BAL_MOIS = :BAL_MOIS")
            ->where("DOS_NUM = :DOS_NUM")
            ->where("AS_comptes_comptepostebilan.AS_TYPEVAL = :AS_TYPEVAL")
            ->where("comptes.CPT_VISIBLE = 1")
            ->setParam("BAL_MOIS", $mois)
            ->setParam("DOS_NUM", $dosNum)
            ->setParam("AS_TYPEVAL", $type);

        if ($cptbNum) {
            $query
                ->select("Balance.codeCompte", "BAL_CUMUL")
                ->where("balance.CPTB_NUM = :CPTB_NUM")
                ->setParam("CPTB_NUM", $cptbNum)
                ->index("Balance.codeCompte");
        } else {
            $query
                ->select("AS_comptes_comptepostebilan.CPTB_NUM", "sum(BAL_CUMUL) as Montant")
                ->groupBy("AS_comptes_comptepostebilan.CPTB_NUM")
                ->index("AS_comptes_comptepostebilan.CPTB_NUM");
        }

        return $query->getAll();
    }

    /**
     * Récupère le résultat par poste d'un dossier
     *
     * @param string $dosNum
     * @param string $mois
     * @param bool $anm1
     * @param bool $cluster
     * @return array|int
     */
    public function getStockRetenu(string $dosNum, string $mois, bool $anm1 = false, bool $cluster = false, int $cptbNum = null)
    {

        if ($anm1) {
            if ($_SESSION["station_DOS_PREMDATECP"] > 0 && $_SESSION["station_DOS_PREMDATECP"] != "0000-00-00") {
                $mois = $_SESSION["station_DOS_PREMDATECP"];
            } elseif ($_SESSION["agip_AG_NUM"]) {
                $mois = StringHelper::DatePlus($mois, array("moisplus" => -12, "dateformat" => "Y-m-00"));
            } else {
                return 0;
            }
        }

        $query = (new QueryBuilder())
            ->select("AS_comptes_comptepostebilan.CPTB_NUM", "sum(StockRetenuBilan) as somme, SUM(comptes.CPT_VISIBLE) as visible")
            ->index("AS_comptes_comptepostebilan.CPTB_NUM")
            ->from("comptepostedetail")
            ->join("dossier", "dossier.DOS_NUM = comptepostedetail.DOS_NUM")
            ->join("station", "station.STA_NUM = dossier.STA_NUM")
            ->join("AS_comptes_comptepostebilan", "AS_comptes_comptepostebilan.codeCompte = comptepostedetail.code_compte")
            ->join("comptes", "comptes.code_compte = comptepostedetail.code_compte")
            ->where("comptepostedetail.Mois = :Mois")
            ->having("visible > 0")
            ->setParam("Mois", $mois)
            ->groupBy("AS_comptes_comptepostebilan.CPTB_NUM");

        if ($cptbNum) {
            $query
                ->where("AS_comptes_comptepostebilan.CPTB_NUM = :CPTB_NUM")
                ->setParam("CPTB_NUM", $cptbNum);
        }

        if (!$cluster && !$anm1) {
            $query
                ->where("dossier.DOS_NUM = :DOS_NUM")
                ->setParam("DOS_NUM", $dosNum);
        } elseif ($cluster && !$anm1) {
            $query
                ->where("station.STA_NUM_CLUSTER = :STA_NUM_CLUSTER")
                ->setParam("STA_NUM_CLUSTER", $_SESSION["station_STA_NUM_CLUSTER"]);
        } elseif ($anm1) {
            if (!$_SESSION["agip_AG_NUM"] || $_SESSION["station_DOS_PREMDATECP"] > 0) {
                $query
                    ->where("station.STA_NUM = :STA_NUM")
                    ->setParam("STA_NUM", $_SESSION["station_STA_NUM"]);
            } else {
                $query
                    ->where("station.LIE_NUM = :LIE_NUM")
                    ->setParam("LIE_NUM", $_SESSION["station_LIE_NUM"]);
            }
        }

        return $query->getAll();
    }

    public function getStockRetenuByCompte($codeCompte = null)
    {
        $query = (new QueryBuilder())
            ->select("AS_comptes_comptepostebilan.CPTB_NUM", "sum(StockRetenuBilan) as somme, SUM(comptes.CPT_VISIBLE) as visible")
            ->index("AS_comptes_comptepostebilan.CPTB_NUM")
            ->from("comptepostedetail")
            ->join("dossier", "dossier.DOS_NUM = comptepostedetail.DOS_NUM")
            ->join("AS_comptes_comptepostebilan", "AS_comptes_comptepostebilan.codeCompte = comptepostedetail.code_compte")
            ->join("comptes", "comptes.CodeCompteAttache = comptepostedetail.code_compte")
            ->where("comptepostedetail.Mois = :Mois")
            ->setParam("Mois", $_SESSION["MoisHisto"])
            ->where("dossier.DOS_NUM = :DOS_NUM")
            ->setParam("DOS_NUM", $_SESSION["station_DOS_NUM"]);

        if ($codeCompte) {
            $query
                ->where("comptes.code_compte = :code_compte")
                ->setParam("code_compte", $codeCompte);

            return $query->get() ?? [];
        }

        return $query->getAll();
    }
}
