<?php

namespace Services;

use Services\AbstractService;

class TableService extends AbstractService
{
    public function setTotalLigneByFamille(array $data, string $familleField, string $ssFamilleField, array $field)
    {

        $famille = $ssFamille = null;
        $monTableau = [];
        $i = 0;

        foreach ($data as $id => $d) {
            $i++;

            if ($d[$ssFamilleField] != $ssFamille && $ssFamille && $d) {
                $this->setLigne($monTableau, $ssFamille, $field, $i, "lnstotal");
            }
            if ($d[$familleField] != $famille && $famille && $d) {
                $this->setLigne($monTableau, $famille, $field, $i, "lntotal");
            }
            $ssFamille = $d[$ssFamilleField];
            $famille = $d[$familleField];

            if(!isset($monTableau["title " . $d[$familleField]])) {
                $monTableau["title_" . $d[$familleField]] = [
                    "libelle" => $d[$familleField],
                    "class" => "EnteteTab",
                ];
            }

            $monTableau[$id] = $d;
            if(count($data) == $i) {
                if($ssFamille) {
                    $this->setLigne($monTableau, $ssFamille, $field, $i, "lnstotal");
                }
                $this->setLigne($monTableau, $famille, $field, $i, "lntotal");
            }
        }

        return $monTableau;
    }

    private function setLigne(array &$montableau, string $label, array $field, int $index, String $class)
    {
        $montableau[$label]["libelle"] = "Total " . $label;
        $montableau[$label]["class"] = $class;
        foreach ($field as $f) {
            $montableau[$label][$f] = 0;
        }
        $montableau["vide_sousfamille_" . $index] = ["libelle" => "&nbsp;", "class" => "vide"];
    }
}
