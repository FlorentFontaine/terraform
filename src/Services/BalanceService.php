<?php

namespace Services;

use Repositories\BalanceRepository;
use Services\AbstractService;

class BalanceService extends AbstractService
{
    private BalanceRepository $balanceRepository;

    private const AMELIORATION = 'amelioration';

    private const DETERIORATION = 'deterioration';

    public function __construct(BalanceRepository $balanceRepository) {
        $this->balanceRepository = $balanceRepository;
    }

    public function getEcartReelPrevuFormatAmeliorationDeterioration(): array
    {
        $postes = $return = [];
        $resultats = $this->balanceRepository->getEcartReelPrevu();
        
        foreach ($resultats as $resultat) {
            if (!isset($postes[$resultat["SsFamille"]][$resultat["realise"]])) {
                $postes[$resultat["SsFamille"]][$resultat["realise"]] = 0;
            }

            $montant = $resultat['realise'] === 'marge' ? (int)$resultat["PrevTauxMontant"] : $resultat["Montant"];

            if ($resultat["Type"] == "Produits") {
                $montant = abs($montant);
            }

            $postes[$resultat["SsFamille"]]["type"] = $resultat["Type"];
            $postes[$resultat["SsFamille"]][$resultat["realise"]] += $montant;
        }

        foreach ($postes as $famille => $montant) {
            if (!isset($montant["realise"])) {
                $montant["realise"] = 0;
            }

            // S'il n'y a pas la clé "prevu", on est dans le cas de la marge
            $montant["prevu"]??= $montant["marge"];

            $ecart = $montant["realise"] && $montant["prevu"] ? $montant["prevu"] - $montant["realise"] : 0;

            if ($montant["prevu"] > $montant["realise"]) {
                $category = ($montant["type"] == "Charges") ? self::AMELIORATION : self::DETERIORATION;
            } elseif($montant["prevu"] < $montant["realise"]) {
                $category = ($montant["type"] == "Charges") ? self::DETERIORATION : self::AMELIORATION;
            } else {
                continue;
            }

            $this->updateReturnArray($return, $category, $famille, $montant, $ecart);
        }
        
        uasort($return["amelioration"], function ($item1, $item2) {
            return $item2['Ecart'] <=> $item1['Ecart'];
        });

        uasort($return["deterioration"], function ($item1, $item2) {
            return $item2['Ecart'] <=> $item1['Ecart'];
        });

        $return["amelioration"] = array_slice($return["amelioration"], 0, 5);
        $return["deterioration"] = array_slice($return["deterioration"], 0, 5);

        return $return;
    }

    private function updateReturnArray(array &$return, string $category, string $famille, array $montant, int $ecart): void
    {
        $return[$category][$famille]["Realise"] = $montant["realise"];
        $return[$category][$famille]["Prevu"] = $montant["prevu"];
        $return[$category][$famille]["Ecart"] = abs($ecart);
    }
}
