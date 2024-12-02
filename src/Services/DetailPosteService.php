<?php

namespace Services;

use Helpers\StringHelper;
use Repositories\BalanceRepository;
use Repositories\DetailPosteRepository;
use Repositories\BilanRepository;
use Services\AbstractService;

require_once __DIR__ . '/../public/dbClasses/AccesDonnees.php';

class DetailPosteService extends AbstractService
{
    private DetailPosteRepository $detailPosteRepository;

    private BilanRepository $bilanRepository;

    private BalanceRepository $balanceRepository;

    public function __construct(DetailPosteRepository $detailPosteRepository, BilanRepository $bilanRepository, BalanceRepository $balanceRepository) {
        $this->detailPosteRepository = $detailPosteRepository;
        $this->bilanRepository = $bilanRepository;
        $this->balanceRepository = $balanceRepository;
    }

    public function getDetail(array $params): array
    {
        $methodName = 'getDetail' . ucfirst($params['type']);
        return $this->$methodName($params, $params["produits"]);
    }

    public function getDetailBilan($params)
    {
        $poste = $this->bilanRepository->getPostesBilan($params["id"]);
        $mesComptes = $this->bilanRepository->getComptesBilan($poste["CPTB_NUM"], $poste["CPTB_SFAMILLE"]);

        return $this->formatDetail($mesComptes, $poste["CPTB_SFAMILLE"], $poste["CPTB_LIB"], $poste["CPTB_SFAMILLE"]);
    }

    public function getDetailPoste($params, $pdt = false): array
    {
        $params["type"] = null;
        if ($pdt) {
            $params["type"] = "vente";
        }

        $mesComptes = $this->detailPosteRepository->getDetailCompteByPoste($params["id"], $params["type"]);

        $poste = reset($mesComptes);
        $famille = $poste["SsFamille"];
        $ssFamille = $poste["Libelle"];

        return $this->formatDetail($mesComptes, $famille, $ssFamille);
    }

    public function getDetailPosteProjection($params): array
    {
        return [];
    }

    private function formatDetail($mesComptes, $famille, $ssFamille, $type = null): array
    {
        $mesResultatsCompte = [];
        $totalResultatsCompte = 0;

        foreach ($mesComptes as $codeCompte => $compte) {
            if($type == "Stock"){
                $monResultat = $this->bilanRepository->getStockRetenuByCompte($codeCompte)["somme"];
            } else {
                $monResultat = $this->balanceRepository->getResultatsCompte($codeCompte)["BAL_CUMUL"];
            }
            $mesResultatsCompte[$famille][$ssFamille][$codeCompte]["numero"] = $compte["numero"];
            $mesResultatsCompte[$famille][$ssFamille][$codeCompte]["libelle"] = $compte["libelle"];
            $montant = $monResultat ?? null;
            $totalResultatsCompte += $montant ? $montant : 0;
            $mesResultatsCompte[$famille][$ssFamille][$codeCompte]["resultat"] = $montant ? round($montant) : "";
        }

        $mesResultatsCompte[$famille][$ssFamille]["total"]["resultat"] = $totalResultatsCompte;

        return $mesResultatsCompte;
    }

}
