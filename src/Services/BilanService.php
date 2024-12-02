<?php

namespace Services;

use Facades\Modules\Commentaire\Commentaire;
use Repositories\BilanRepository;
use Services\AbstractService;

class BilanService extends AbstractService
{
    private BilanRepository $bilanRepository;

    private TableService $tableService;

    private array $specificActifPoste = [
        "6" => "stocks", // Stocks - Produits pétroliers
        "7" => "stocks", // Stocks - Ventes boutique
        "8" => "stocks", // Stocks - Bar buffet
        "9" => "stocks", // Stocks - Ventes automobiles
        "61" => "stocks", // Stocks - Speedy
        "16" => "withSens", // Fournisseurs et autres créances
        "17" => "positif", // Etat impôts sur bénéfice
        "18" => "withSens", // Etat-TVA
        "25" => "withSens", // Banque
    ];

    private array $specificPassifPoste = [
        "35" => "resultat", // Résultat comptable
        "46" => "withSens", // Découvert bancaire
        "51" => "withSens", // Dettes fournisseurs autres
        "53" => "withSens", // Personnel
        "54" => "withSens", // Organismes sociaux
        "55" => "positif", // Impôts et taxes
        "56" => "withSens", // Etat-TVA
        "57" => "withSens", // Autres dettes et charges à payer
    ];

    public function __construct(BilanRepository $bilanRepository, TableService $tableService)
    {
        $this->bilanRepository = $bilanRepository;
        $this->tableService = $tableService;
    }

    public function getBilan(): array
    {
        $mois = $_SESSION["MoisHisto"];
        $dosNum = $_SESSION["station_DOS_NUM"];

        $mesPostesActif = $this->getActif($dosNum, $mois);
        $mesPostesPassif = $this->getPassif($dosNum, $mois);

        return [
            'mesPostesActif' => $mesPostesActif,
            'mesPostesPassif' => $mesPostesPassif
        ];
    }

    public function getActif($dosNum, $mois, $cluster = false): array
    {

        $mesPostesActif = $this->bilanRepository->getAllPostesByType("actif");
        $mesPostesActif = $this->tableService->setTotalLigneByFamille($mesPostesActif, "CPTB_FAMILLE", "CPTB_SFAMILLE", ["brut", "amort", "net"]);
        
        $monActifBrut = $this->bilanRepository->getPosteResultat($dosNum, $mois, "brut");
        $monActifProv = $this->bilanRepository->getPosteResultat($dosNum, $mois, "amort");

        $mesPostesActif["total"] = [
            "CPTB_NUM" => "total",
            "libelle" => "Total Actif",
            "class" => "EnteteTab",
            "brut" => 0,
            "amort" => 0,
            "net" => 0,
        ];
        
        foreach ($mesPostesActif as $cptbNum => $posteActif) {
            if(is_int($cptbNum)) {
                $data = [
                    "key" => $posteActif["CPTB_NUM"],
                    "value" => $posteActif["CPTB_LIB"]
                ];

                $mesPostesActif[$cptbNum]["libelle"] =  "<span data-type='bilan' data-id='" . $posteActif["CPTB_NUM"] . "' class='detail_tooltip' >&#8505;</span>" . Commentaire::actionOnbilan($data);

                $mesPostesActif[$cptbNum]["brut"] = $this->calculPosteActif($monActifBrut[$cptbNum], $monActifProv[$cptbNum], $dosNum, $mois, $cluster);
                $mesPostesActif[$cptbNum]["amort"] = $monActifProv[$cptbNum]["Montant"];
                $mesPostesActif[$cptbNum]["net"] = $mesPostesActif[$cptbNum]["brut"] - $mesPostesActif[$cptbNum]["amort"];

                if($posteActif["CPTB_SFAMILLE"] != $posteActif["CPTB_FAMILLE"]) {
                    $mesPostesActif[$posteActif["CPTB_SFAMILLE"]]["brut"] += $mesPostesActif[$cptbNum]["brut"];
                    $mesPostesActif[$posteActif["CPTB_SFAMILLE"]]["amort"] += $mesPostesActif[$cptbNum]["amort"];
                    $mesPostesActif[$posteActif["CPTB_SFAMILLE"]]["net"] += $mesPostesActif[$cptbNum]["net"];
                }
                
                $mesPostesActif[$posteActif["CPTB_FAMILLE"]]["brut"] += $mesPostesActif[$cptbNum]["brut"];
                $mesPostesActif[$posteActif["CPTB_FAMILLE"]]["amort"] += $mesPostesActif[$cptbNum]["amort"];
                $mesPostesActif[$posteActif["CPTB_FAMILLE"]]["net"] += $mesPostesActif[$cptbNum]["net"];
                
                $mesPostesActif["total"]["brut"] += $mesPostesActif[$cptbNum]["brut"];
                $mesPostesActif["total"]["amort"] += $mesPostesActif[$cptbNum]["amort"];
                $mesPostesActif["total"]["net"] += $mesPostesActif[$cptbNum]["net"];
            }
        }

        return  $mesPostesActif;
    }

    public function getPassif($dosNum, $mois): array
    {
        $mesPostesPassif = $this->bilanRepository->getAllPostesByType("passif");
        $mesPostesPassif = $this->tableService->setTotalLigneByFamille($mesPostesPassif, "CPTB_FAMILLE", "CPTB_SFAMILLE", ["net"]);

        $monPassif = $this->bilanRepository->getPosteResultat($dosNum, $mois, "net");

        $mesPostesPassif["total"] = [
            "CPTB_NUM" => "total",
            "libelle" => "Total Passif",
            "class" => "EnteteTab",
            "net" => 0,
        ];

        foreach ($mesPostesPassif as $cptbNum => $postePassif) {
            if(is_int($cptbNum)) {
                $data = [
                    "key" => $postePassif["CPTB_NUM"],
                    "value" => $postePassif["CPTB_LIB"]
                ];

                $mesPostesPassif[$cptbNum]["libelle"] =  "<span data-type='bilan' data-id='" . $postePassif["CPTB_NUM"] . "' class='detail_tooltip' >&#8505;</span>" .  Commentaire::actionOnbilan($data);
                
                $mesPostesPassif[$cptbNum]["net"] = $monPassif[$cptbNum] && $monPassif[$cptbNum]["Montant"] ? $this->calculPostePassif($monPassif[$cptbNum], $dosNum, $mois) : 0;
                
                if($postePassif["CPTB_SFAMILLE"] != $postePassif["CPTB_FAMILLE"]) {
                    $mesPostesPassif[$postePassif["CPTB_SFAMILLE"]]["net"] += $mesPostesPassif[$cptbNum]["net"];
                }

                $mesPostesPassif[$postePassif["CPTB_FAMILLE"]]["net"] += $mesPostesPassif[$cptbNum]["net"];

                $mesPostesPassif["total"]["net"] += $mesPostesPassif[$cptbNum]["net"];
            }
        }

        return $mesPostesPassif;
    }

    private function calculPosteActif($monActifBrut, &$monActifProv, $dosNum, $mois, $cluster = false)
    {
        $monActifProv["Montant"] = $monActifProv["Montant"] ?? 0;
        $cptbNum = $monActifBrut["CPTB_NUM"];

        if (in_array($cptbNum, array_keys($this->specificActifPoste))) {
            if ($this->specificActifPoste[$cptbNum] == "stocks") {
                $mesStocks = $this->bilanRepository->getStockRetenu($dosNum, $mois, false, $cluster, $cptbNum);
                $monActifBrut["Montant"] = round($mesStocks[$cptbNum]["somme"]);

            } elseif ($this->specificActifPoste[$cptbNum] == "withSens") {
                $accounts = $this->bilanRepository->getPosteResultat($dosNum, $mois, "brut", $cptbNum);
                $monActifBrut["Montant"] = round($this->getMontantPosteBilanActif($accounts));

            } elseif ($this->specificActifPoste[$cptbNum] == "positif" && $monActifBrut["Montant"] < 0) {
                $monActifBrut["Montant"] = 0;
            }
        } else {
            $monActifBrut["Montant"] = round($monActifBrut["Montant"]);
        }
        
        $monActifProv["Montant"] = -$monActifProv["Montant"];

        return $monActifBrut["Montant"];
    }

    private function calculPostePassif($monPassif, $dosNum, $mois) {

        $cptbNum = $monPassif["CPTB_NUM"];

        
        if (in_array((int) $cptbNum, array_keys($this->specificPassifPoste))) {
            if ($this->specificPassifPoste[$cptbNum] == "resultat") {
                $resultatComptable = $this->bilanRepository->getResultat($dosNum, $mois);
                $monPassif["Montant"] = round($resultatComptable['BALI_RES']);

            } elseif ($this->specificPassifPoste[$cptbNum] == "withSens") {
                $accounts = $this->bilanRepository->getPosteResultat($dosNum, $mois, "net", $cptbNum);
                $monPassif["Montant"] = round(self::getMontantPosteBilanPassif($accounts));
            } elseif ($this->specificPassifPoste[$cptbNum] == "positif" && $monPassif["Montant"] > 0) {
                $monPassif["Montant"] = 0;
            }
        }else {
            $monPassif["Montant"] = -$monPassif["Montant"];
        }

        return $monPassif["Montant"];
    }

    private function getMontantPosteBilanActif($comptes)
    {
        $cumul = 0;

        foreach ($comptes as $MonCodeCompte => $Montant) {
            if (isset($Montant["Sens"]) && $Montant["Sens"] == "+" && $Montant["BAL_CUMUL"] < 0) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = 0;
            }

            $comptes[$MonCodeCompte]["BAL_CUMUL"] = round($comptes[$MonCodeCompte]["BAL_CUMUL"]);
            $cumul += $comptes[$MonCodeCompte]["BAL_CUMUL"];
        }

        return $cumul;
    }

    private function getMontantPosteBilanPassif($comptes)
    {
        $cumul = 0;

        foreach ($comptes as $MonCodeCompte => $Montant) {
            if (isset($Montant["Sens"]) && $Montant["Sens"] == "-" && $Montant["BAL_CUMUL"] < 0) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = -round($Montant["BAL_CUMUL"]);
            } elseif (!isset($Montant["Sens"]) || !$Montant["Sens"]) {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = -round($Montant["BAL_CUMUL"]);
            } else {
                $comptes[$MonCodeCompte]["BAL_CUMUL"] = 0;
            }

            $cumul += $comptes[$MonCodeCompte]["BAL_CUMUL"];
        }

        return $cumul;
    }
}
