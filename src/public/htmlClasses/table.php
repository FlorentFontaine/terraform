<?php

class table
{

    /**
     * @param $TdValues
     * @param $TrAttribus
     * @param $nbTd
     * @param $opt
     *          Options Possibles :
     *              "debug_cletr" : ajoute un <TD> avec la clé passée en valeur
     *              "colorsigne"[] : on colore les nombres en rouge ou en vert selon le "+" ou "-"
     * @return string
     */
    static function getLine($TdValues, $TrAttribus = null, $nbTd = 0, $opt = array())
    {
        if (!$TdValues) {
            return '';
        }

        if ($nbTd > 0) {
            $TabRemp = array();

            for ($i = 0; $i < $nbTd; $i++) {
                $TabRemp[$i] = array("" => array("class" => "colvide"));
            }

            $MtabTran = array_diff_key($TabRemp, $TdValues);

            foreach ($MtabTran as $cle => $v) {
                $TdValues[$cle] = $v;
            }

            ksort($TdValues);
        }

        $Return = "<tr";

        if ($TrAttribus) {
            //application des attrributs du tr
            foreach ($TrAttribus as $UnTrAttribus => $UneValeur) {
                $Return .= " $UnTrAttribus=\"$UneValeur\" ";
            }
        }

        $Return .= ">";

        $TdNum = 0;
        $NbSpan = 0;

        if (isset($opt['debug_cletr']) && $opt['debug_cletr']) {
            $Return .= "<td>" . $opt['debug_cletr'] . "</td>";
        }

        if (!is_array($TdValues)) {
            $TdNum = 1;
            $Return .= "<td>" . $TdValues . "</td>";
        } else {
            $NbSpanBoucle = 0;

            foreach ($TdValues as $UnTd) {
                $TdNum++;
                if ($NbSpanBoucle) {
                    $NbSpanBoucle--;
                }

                if (
                    isset($opt["colvide"]) && is_array($opt["colvide"])
                    && $TdNum > $NbSpan
                    && in_array($TdNum - 1, $opt["colvide"])
                ) {
                    $Return .= "<td class='colvide'></td>";
                }

                if ($NbSpanBoucle == 0) {
                    if ($UnTd) {
                        if (is_array($UnTd)) {
                            //normalement un passage, car la clé, c'est le champ visible
                            foreach ($UnTd as $UnTdValues => $UnTdAttribus) {
                                $Return .= "<td ";

                                if (!$UnTdAttribus) {
                                    $UnTdAttribus = array();
                                }

                                // On colore les nombres en rouge ou en vert selon le "+" ou "-"
                                if (isset($opt["colorsigne"]) && in_array($TdNum, $opt["colorsigne"])) {
                                    if (!isset($UnTdAttribus["style"]) || !$UnTdAttribus["style"]) {
                                        $UnTdAttribus["style"] = "";
                                    }

                                    if (
                                        (
                                            (!isset($opt["colorreverse"]) || !$opt["colorreverse"])
                                            && strpos($UnTdValues, "-") !== false
                                        ) || (
                                            (isset($opt["colorreverse"]) && $opt["colorreverse"])
                                            && strpos($UnTdValues, "-") === false
                                        )
                                    ) {
                                        $UnTdAttribus["style"] .= "color:#FF0000";
                                    } else {
                                        $UnTdAttribus["style"] .= "color:#00B874";
                                    }
                                }

                                //application des atrributs du td
                                foreach ($UnTdAttribus as $TdAttribus => $UneValeur) {
                                    $Return .= " $TdAttribus=\"$UneValeur\" ";

                                    if ($TdAttribus == "colspan") {
                                        $NbSpan = (int)$UneValeur;
                                        $NbSpanBoucle = (int)$UneValeur;
                                    }
                                }

                                $Return .= ">";

                                if ($UnTdValues == "") {
                                    $Return .= "";
                                }

                                $Return .= utf8_encode($UnTdValues);
                                $Return .= "</td>";
                            }
                        } else {
                            $Style = "";

                            if (isset($opt["colorsigne"]) && in_array($TdNum, $opt["colorsigne"])) { //voir l'inverse pour les charges !!!!!!
                                if (
                                    (
                                        (!isset($opt["colorreverse"]) || !$opt["colorreverse"])
                                        && strpos($UnTd, "-") !== false
                                    ) || (
                                        (isset($opt["colorreverse"]) && $opt["colorreverse"])
                                        && strpos($UnTd, "-") === false
                                    )
                                ) {
                                    $Style = " style='color:#FF0000' ";
                                } else {
                                    $Style = " style='color:#00B874' ";
                                }
                            }

                            $Return .= "<td$Style>" . utf8_encode($UnTd) . "</td>";
                        }
                    } else {
                        $Return .= "<td class='colvide'></td>";
                    }
                }
            }
        }

        // On complète les case vide s'il y en a et que $nbTd est renseigné
        for ($i = $TdNum; $i < $nbTd - $NbSpan; $i++) {
            $Return .= "<td class='colvide'></td>";
        }

        $Return .= "</tr>\n";

        return $Return;
    }
}
