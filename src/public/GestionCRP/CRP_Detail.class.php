<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../PrevBack/prev.class.php';

/**
 * Classe CRP_Detail
 * @author Myriam
 * @since 14/05/2014
 * @name CRP_Detail
 */
class CRP_Detail
{
    /**
     * Retrieves the details of the TabCRP.
     *
     * @param array $d The data needed to retrieve the details.
     * @return array The details of the TabCRP.
     */
    static function get_TabCRPDetail(array $d): array
    {
        $Where = [
            "and" =>
                [
                    "comptePoste.Type" => " = '" . $d['Type'] . "' "
                ],
            [
                "comptePoste.Famille" => " != 'Carburants' "
            ]
        ];

        $tri = ["ordre" => "ASC"];

        $MesPostes = dbAcces::getPosteVisible($Where, $tri);

        $PremF = $PremSF = true;
        $FaireSTotaux = false;
        $SsFamilleDef = $FamilleDef = '';

        // Initialisation du tableau avec ligne total + sous-total de chaque poste
        foreach ($MesPostes as $codePoste => $UneLignePoste) {
            $UneLigneTableau = [];

            //pour savoir combien il y a de sous familles
            if ($UneLignePoste["SsFamille"] != $SsFamilleDef && $UneLignePoste["Famille"] == $FamilleDef) {
                $FaireSTotaux = true;
            }

            // Changement de sous famille
            if ($UneLignePoste["SsFamille"] != $SsFamilleDef) {
                if (!$PremSF && $SsFamilleDef != $FamilleDef && $FaireSTotaux) {
                    //LnsTotal
                    $Nom = explode("||#||", $SsFamilleDef);
                    $Nom = (count($Nom) > 1) ? "Sous total :" : "Total " . $SsFamilleDef . " :";

                    $UneLigneTableau[] = [
                        $Nom => [
                            "libelle" => 1, 'align' => 'right', 'style' => 'font-weight: bolder'
                        ]
                    ];
                    $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = [];
                }

                $SsFamilleDef = $UneLignePoste["SsFamille"];
            }

            // Changement de famille
            if ($UneLignePoste["Famille"] != $FamilleDef) {
                if (!$PremF) {
                    str_replace("PERSONNEL ET DE GERANCE", "PERS. ET DE GER.", $FamilleDef);
                    $FamilleDefStr = str_replace("VENTES MARCHANDISES", "BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE", $FamilleDef);
                    //LnTotal
                    $UneLigneTableau[] = [
                        "TOTAL " . $FamilleDefStr . " : " => [
                            "libelle" => 1, 'style' => 'font-weight: bolder'
                        ]
                    ];
                    $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = [];

                    //Ln vide
                    $UneLigneTableau[] = ["&nbsp;" => ""];
                    $MesLignesTableau["VIDE" . $FamilleDef] = $UneLigneTableau;
                    $UneLigneTableau = [];

                    if ($FamilleDef == "Chiffres d'affaires") {
                        $MesLignesTableau = compChargesProd::setTabMarges($MesLignesTableau);
                    }
                }

                //ln nom famille
                $FamilleDefStr = str_replace("VENTES MARCHANDISES", "BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE", $UneLignePoste["Famille"]);
                $UneLigneTableau[] = array($FamilleDefStr => array("libelle" => 1, "style" => "font-weight: bolder;text-align:left", 'class' => "tdflotte", 'colspan' => "6"));

                $MesLignesTableau["TITRE" . $UneLignePoste["Famille"]] = $UneLigneTableau;
                $UneLigneTableau = [];

                $FamilleDef = $UneLignePoste["Famille"];
                $PremSF = true;
                $FaireSTotaux = false;
            }

            $UneLigneTableau[] = [
                $UneLignePoste["Libelle"] => [
                    "libelle" => 1, "align" => "left"
                ]
            ];

            $MesLignesTableau[$codePoste] = $UneLigneTableau;

            if ($PremSF) {
                $PremSF = false;
            }
            if ($PremF) {
                $PremF = false;
            }
        }

        //LnsTotal
        if ($FaireSTotaux) {
            $Nom = explode("||#||", $SsFamilleDef);
            $Nom = (count($Nom) > 1) ? "Sous total :" : "Total " . $SsFamilleDef . " :";

            $UneLigneTableau = [];
            $UneLigneTableau[] = [
                $Nom => [
                    "libelle" => 1, 'align' => 'right', 'style' => 'font-weight: bolder'
                ]
            ];

            $MesLignesTableau["STOTAL" . $SsFamilleDef] = $UneLigneTableau;
        }

        //LnTotal
        $UneLigneTableau = [];
        $UneLigneTableau[] = array("TOTAL " . $FamilleDef . " :" => array("libelle" => 1, 'style' => 'font-weight: bolder'));
        $MesLignesTableau["TOTAL" . $FamilleDef] = $UneLigneTableau;

        $MomPrev = db_CRP_Detail::select_CRP_Detail($d);

        $UnTotalAnnuel = $UnTotalCor = $UnTotalTaux = $UnTotalMontant = $UnTotalMargeMois = 0;
        $UnSTotalAnnuel = $UnSTotalTaux = $UnSTotalMontant = $UnSTotalMargeMois = 0;
        $BigTotal = [
            "annuel" => 0,
            "taux" => 0,
            "MargeMois" => 0,
            "montant" => 0,
            "cor" => 0,
        ];
        $PremTotal = true;

        foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb) {
            if (is_numeric($codeCompte)) {
                $cle = $MomPrev[$codeCompte]["SAI_CLE"];
                $annuel = $MomPrev[$codeCompte]["Annuel"];
                $cor = $MomPrev[$codeCompte]["Correction"];
                $montant = $MomPrev[$codeCompte]["Montant"];
                $Taux = $MomPrev[$codeCompte]["PrevTaux"];

                // C'est une ligne d'un poste
                $UneLigneDb[] = [
                    "<input type='text' " . $_SESSION["User"]->getAut("prev", "Cle") . " name='cle[$codeCompte]' value='" . $cle . "' class='gapiareamontant' style='width:40px' />"
                    => ["align" => "right"]
                ];
                $UneLigneDb[] = [
                    "<input type='text' " . $_SESSION["User"]->getAut("prev", "Annuel") . " name='annuel[$codeCompte]' value='" . StringHelper::NombreFr($annuel, 0) . "' class='gapiareamontant' />"
                    => ["align" => "right"]
                ];
                $UneLigneDb[] = [
                    "<input type='hidden' name='compte[$codeCompte]' value=''" . $MesPostes[$codeCompte]["Libelle"] . "' />
                    <input type='hidden' name='montant[$codeCompte]' value=''" . StringHelper::NombreFr($montant, 0) . "' />
                    <input type='hidden' name='Periode[$codeCompte]' value=''" . $_SESSION["MoisHisto"] . "' />" . StringHelper::NombreFr($montant, 0)
                    => ["align" => "right"]
                ];

                $MargeMois = 0;
                if ($d['Type'] == "Produits") {
                    if ($PremTotal) {
                        $UneLigneDb[] = [
                            "<input type='text' " . $_SESSION["User"]->getAut("prev", "PrevTaux") . " name='prevtaux[$codeCompte]' value='" . StringHelper::NombreFr($Taux, 0) . "' class='gapiareamontant' />"
                            => ["align" => "right"]
                        ];
                    } else {
                        $UneLigneDb[] = "";
                    }

                    $MargeMois = ($montant * $Taux) / 100;

                    $UneLigneDb[] = [
                        StringHelper::NombreFr($MargeMois, 0) => [
                            "align" => "right"
                        ]
                    ];
                } else {
                    $UneLigneDb[] = "";
                    $UneLigneDb[] = "";
                }

                $UnTotalAnnuel += $annuel;
                $UnTotalTaux += $annuel * ($Taux / 100);
                $UnTotalMontant += $montant;
                $UnTotalCor += $cor;
                $UnTotalMargeMois += $MargeMois;

                $UnSTotalAnnuel += $annuel;
                $UnSTotalTaux += $annuel * ($Taux / 100);
                $UnSTotalMontant += $montant;
                $UnSTotalMargeMois += $MargeMois;
            } else {
                if (stristr($codeCompte, "STOTAL")) {
                    $UneLigneDb[] = [];
                    $UneLigneDb[] = StringHelper::NombreFr($UnSTotalAnnuel, 0);
                    $UneLigneDb[] = StringHelper::NombreFr($UnSTotalMontant, 0);

                    if ($d['Type'] == "Produits") {
                        $UneLigneDb[] = StringHelper::NombreFr($UnSTotalTaux, 0);
                        $UneLigneDb[] = StringHelper::NombreFr($UnSTotalMargeMois, 0);
                    } else {
                        $UneLigneDb[] = "";
                        $UneLigneDb[] = "";
                    }

                    $UnSTotalAnnuel = 0;
                    $UnSTotalTaux = 0;
                    $UnSTotalMontant = 0;
                    $UnSTotalMargeMois = 0;
                } elseif (stristr($codeCompte, "TOTAL")) {
                    $UneLigneDb[] = [];
                    $UneLigneDb[] = StringHelper::NombreFr($UnTotalAnnuel, 0);
                    $UneLigneDb[] = StringHelper::NombreFr($UnTotalMontant, 0);

                    if ($d['Type'] == "Produits") {
                        $UneLigneDb[] = StringHelper::NombreFr($UnTotalTaux, 0);
                        $UneLigneDb[] = StringHelper::NombreFr($UnTotalMargeMois, 0);
                    } else {
                        $UneLigneDb[] = "";
                        $UneLigneDb[] = "";
                    }

                    if ($PremTotal && $d['Type'] == "Produits") {
                        $UnTotalAnnuel = 0;
                        $UnTotalMontant = 0;
                    }

                    $BigTotal["annuel"] += $UnTotalAnnuel;
                    $BigTotal["taux"] += $UnTotalTaux;
                    $BigTotal["MargeMois"] += $UnTotalMargeMois;
                    $BigTotal["montant"] += $UnTotalMontant;
                    $BigTotal["cor"] += $UnTotalCor;

                    $UnTotalAnnuel = 0;
                    $UnTotalCor = 0;
                    $UnTotalTaux = 0;
                    $UnTotalMontant = 0;
                    $UnTotalMargeMois = 0;
                    $UnSTotalAnnuel = 0;
                    $UnSTotalTaux = 0;
                    $UnSTotalMontant = 0;
                    $UnSTotalMargeMois = 0;
                    $PremTotal = false;
                }
            }
        }

        $MesLignesTableau[] = array(
            0 => array("&nbsp;" => array("align" => "right", "style" => "border-left:1px solid grey")),
            1 => array("&nbsp;" => array("align" => "right")),
            2 => array("&nbsp;" => array("align" => "right")),
            3 => array("&nbsp;" => array("align" => "right")),
            4 => array("&nbsp;" => array("align" => "right")),
            5 => array("&nbsp;" => array("align" => "right"))
        );

        $MesLignesTableau["BIGTOTAL"] = array(0 => array("Total pr&eacute;visionnel " . $d['Type'] => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
        $MesLignesTableau["BIGTOTAL"][] = "";
        $MesLignesTableau["BIGTOTAL"][] = StringHelper::NombreFr($BigTotal["annuel"] + $BigTotal["taux"], 0);
        $MesLignesTableau["BIGTOTAL"][] = StringHelper::NombreFr($BigTotal["montant"] + $BigTotal["MargeMois"], 0);

        if ($d['Type'] == "Produits") {
            $_SESSION["CRP_BIGTOTAL_PREVPRODUITS"] = $MesLignesTableau["BIGTOTAL"];
        } elseif ($d['Type'] == "Charges") {
            $MesLignesTableau[] = array(0 => array("&nbsp;" => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
            $MesLignesTableau["RESULTATTOTAL"] = array(0 => array("Résultat" => array("align" => "right", "style" => "font-weight: bolder;border-left:1px solid grey")));
            $MesLignesTableau["RESULTATTOTAL"][] = "";
            $MesLignesTableau["RESULTATTOTAL"][] = StringHelper::NombreFr(StringHelper::Texte2Nombre($_SESSION["CRP_BIGTOTAL_PREVPRODUITS"][2]) - $BigTotal["annuel"], 0);
            $MesLignesTableau["RESULTATTOTAL"][] = StringHelper::NombreFr(StringHelper::Texte2Nombre($_SESSION["CRP_BIGTOTAL_PREVPRODUITS"][3]) - $BigTotal["montant"], 0);
            $MesLignesTableau["RESULTATTOTAL"][] = array("" => array("align" => "right"));
            $MesLignesTableau["RESULTATTOTAL"][] = array("" => array("align" => "right"));
        }

        return $MesLignesTableau;
    }

    /**
     * Verifies the data provided for a TabCRP and updates the error array with any missing fields.
     *
     * @param array $d The data to be verified.
     * @param array &$e The array to store the errors.
     * @return void
     */
    static function verifier_donnees(array $d, array &$e)
    {
        if (!isset($d["CRP_NUM"]) || !$d["CRP_NUM"]) {
            $e["CRP_NUM"] = true;
        }

        if (!isset($d["STA_NUM"]) || !$d["STA_NUM"]) {
            $e["STA_NUM"] = true;
        }

        if (!isset($d["compte"]) || !$d["compte"]) {
            $e["compte"] = true;
        }

        if (!isset($d["CRP_NB_MOIS"]) || !$d["CRP_NB_MOIS"]) {
            $e["CRP_NB_MOIS"] = true;
        }
    }

    /**
     * Registers the CRP Detail.
     *
     * @param array $d The data needed to register the CRP Detail.
     * @param array &$e If there are any errors, they will be stored in this parameter.
     * @return mixed The CRP_NUM if successful, otherwise false.
     */
    static function enregister_CRP_Detail(array $d, array &$e = [])
    {
        self::verifier_donnees($d, $e);

        //Aucune erreur
        if (empty($e)) {
            $accountsIDs = array_keys($d['compte']);

            $crit = [
                "CRP_NUM" => $d['CRP_NUM'],
                "STA_NUM" => $d['STA_NUM'],
                "codePoste" => $accountsIDs
            ];

            self::supprimer_CRPDetail($crit);
            
            $set = [];
            foreach ($accountsIDs as $id) {
                $crit["codePoste"] = $id;
                $crit["Annuel"] = StringHelper::Texte2Nombre($d["annuel"][$id]);
                $crit["Montant"] = round(StringHelper::Texte2Nombre($d["annuel"][$id]) / $d["CRP_NB_MOIS"]);
                $crit["SAI_CLE"] = StringHelper::Texte2Nombre($d["cle"][$id]);
                $crit["PrevTaux"] = StringHelper::Texte2Nombre($d["prevtaux"][$id]);

                $set[] = $crit;
            }

            $TAB_CLE_CRP = db_CRP_Detail::ui_CRP_Detail($set);

            if (!$TAB_CLE_CRP) {
                return false;
            }
        }

        return $d['CRP_NUM'];
    }

    /**
     * Méthode de suppression de données dans la table GestionCRP
     * @param <int> $d - tableau déterminant les lignes à supprimer
     * @since 14/05/2014
     * @name supprimer_CRPDetail
     * @author Myriam
     */
    static function supprimer_CRPDetail($d)
    {
        db_CRP_Detail::delete_CRP_Detail($d);
    }

    /**
     * Méthode de copie des données du CRP précédent
     * @param <int> $d - tableau déterminant les lignes à supprimer
     */
    static function copie_CRP_Detail($d, &$e)
    {
        extract($d);

        if (!$CRP_NUM) {
            throw new Exception("copie_CRP_Detail > Variable obligatoire : CRP_NUM :" . $CRP_NUM);
        }

        //Récupération du CRP en cours
        $param = array(
            "tabCriteres" => array(
                "STA_NUM" => $_SESSION["station_STA_NUM"],
                "CRP_NUM" => $d["CRP_NUM"]
            )
        );
        $CRP = db_CRP::select_CRP($param);
        $CRP = $CRP[$CRP_NUM];

        $CRP_DBT = $CRP["CRP_DBT"];
        $CRP_FIN = $CRP["CRP_FIN"];


        //Récupération du CRP_NUM précédent
        $param = array(
            "tabCriteres" => array(
                "STA_NUM" => $_SESSION["station_STA_NUM"],
                "CRP_NUM" => $CRP_NUM,
                "CRP_FIN" => $CRP_DBT
            ),
            "tabOP" => array(
                "CRP_NUM" => "!=",
                "CRP_FIN" => "<"
            ),
            "triRequete" => " order by CRP_FIN DESC LIMIT 0,1 "
        );
        $LastCRP = db_CRP::select_CRP($param);
        $array_keys = array_keys($LastCRP);
        $CRP_NUM_PREC = $array_keys[0];
        $LastCRP = $LastCRP[$CRP_NUM_PREC];

        //Récupération des données du CRP précédent
        $param = array("tabCriteres" => array(
            "CRP_NUM" => $CRP_NUM_PREC,
            "STA_NUM" => $STA_NUM
        ));
        $CRPprec_Detail = db_CRP_Detail::select_CRP_Detail($param);

        //Enregistrement des données dans le CRP en cours
        if ($CRPprec_Detail) {
            foreach ($CRPprec_Detail as $codePoste => $value) {
                $crit = array(
                    "CRP_NUM" => $CRP_NUM,
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "codePoste" => $codePoste
                );

                if ($d["cle_checked"]) {
                    $crit["SAI_CLE"] = $value["SAI_CLE"];
                }

                if ($d["valeur_checked"]) {
                    $crit["Annuel"] = $value["Annuel"];
                    $crit["Montant"] = round($value["Annuel"] / $CRP["CRP_NB_MOIS"]);
                    $crit["PrevTaux"] = $value["PrevTaux"];
                }

                $TAB_CLE_CRP = db_CRP_Detail::ui_CRP_Detail($crit);

                if (!$TAB_CLE_CRP) {
                    $e = "Erreur lors de l'enregistrement.";
                    return false;
                }
            }

            if ($d["valeur_checked"]) {
                //Enregistrement sur tous les mois du CRP
                $param = array(
                    "CRP_NUM" => $CRP_NUM,
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                );
                Previsionnel::RefactAllPrev($param);
            }
        } else {
            $e = "Il n'y a pas de CRP pr&eacute;c&eacute;dents.";
            return false;
        }

        return $CRP_NUM;
    }

    /**
     * Méthode de recalcul des données mensuel du CRP
     * @param <int> $d - tableau déterminant les lignes à supprimer
     */
    static function recalcul_CRP_Detail($d, &$e)
    {
        extract($d);

        if (!$CRP_NUM) {
            throw new Exception("recalcul_CRP_Detail > Variable obligatoire : CRP_NUM :" . $CRP_NUM);
        }

        //Récupération du CRP en cours
        $param = array(
            "tabCriteres" => array(
                "STA_NUM" => $_SESSION["station_STA_NUM"],
                "CRP_NUM" => $d["CRP_NUM"]
            )
        );
        $CRP = db_CRP::select_CRP($param);
        $CRP = $CRP[$CRP_NUM];

        //Récupération des données du CRP en cours
        $param = array("tabCriteres" => array(
            "CRP_NUM" => $CRP_NUM,
            "STA_NUM" => $_SESSION["station_STA_NUM"]
        ));
        $CRP_Detail = db_CRP_Detail::select_CRP_Detail($param);

        //Enregistrement des données dans le CRP en cours
        if ($CRP_Detail) {
            foreach ($CRP_Detail as $codePoste => $value) {
                $crit = array(
                    "CRP_NUM" => $CRP_NUM,
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "codePoste" => $codePoste
                );

                $crit["SAI_CLE"] = $value["SAI_CLE"];

                $crit["Annuel"] = $value["Annuel"];
                $crit["Montant"] = round($value["Annuel"] / $CRP["CRP_NB_MOIS"]);
                $crit["PrevTaux"] = $value["PrevTaux"];

                $TAB_CLE_CRP = db_CRP_Detail::ui_CRP_Detail($crit);

                if (!$TAB_CLE_CRP) {
                    $e = "Erreur lors de l'enregistrement.";
                    return false;
                }
            }

            //Enregistrement sur tous les mois du CRP
            $param = array(
                "CRP_NUM" => $CRP_NUM,
                "STA_NUM" => $_SESSION["station_STA_NUM"],
            );
            Previsionnel::RefactAllPrev($param);


        } else {
            $e = "Il n'y a pas de CRP pr&eacute;c&eacute;dents.";
            return false;
        }

        return $CRP_NUM;
    }
}

/**
 * Classe d'accès aux données de la table CRP_Detail
 */
class db_CRP_Detail extends dbAcces
{
    /**
     * Décrire ici les noms des champs pour Insert / Update
     * sous la forme : 
     * "nomChamp" => 1, "nomChamp2" => 1, etc...
     */
    private static array $tabCritUI_CRPDetail = [
        "STA_NUM" => 1,
        "CRP_NUM" => 1,
        "codePoste" => 1,
        "SAI_CLE" => 1,
        "Annuel" => 1,
        "PrevTaux" => 1,
        "Montant" => 1
    ];

    /**
     * Méthode pour sélectionner les détails CRP correspondants aux critères spécifiés.
     *
     * @param array $d Tableau contenant les paramètres de recherche.
     *        - tabCriteres (array) : Tableau associatif contenant les critères de recherche.
     *        - tabOP (array) : Tableau associatif contenant les opérations de comparaison pour chaque critère.
     *
     * @return array Tableau contenant les détails CRP correspondants aux critères spécifiés.
     *               Les détails sont organisés par le champ "codePoste" en tant que clé.
     *               Si aucun détail ne correspond aux critères, un tableau vide est retourné.
     */
    static function select_CRP_Detail(array $d = []): array
    {
        if (!isset($d['tabCriteres']) || !$d['tabCriteres']) {
            return [];
        }

        $join = $triRequete = $where = "";

        //Boucle de construction du where
        foreach ($d['tabCriteres'] as $nomChamp => $valeur) {
            if (!isset($d['tabOP'][$nomChamp]) || !$d['tabOP'][$nomChamp]) {
                $operation = " = "; //L'operation par défaut
            } else {
                $operation = $d['tabOP'][$nomChamp];
            }

            $where .= " And " . $nomChamp . " " . $operation . " \"" . $valeur . "\" ";
        }

        //CONSTRUCTION DE LA REQUETE SQL
        $sql = "SELECT * FROM crp_detail
            " . $join . "
            WHERE 1 " . $where . " " . $triRequete;

        $tabRetour = [];

        Database::query($sql);
        while ($ligne = Database::fetchArray()) {
            $tabRetour[$ligne["codePoste"]] = $ligne;
        }

        return $tabRetour;
    }

    /**
     * Insert CRP_Detail records based on provided data.
     *
     * @param array $d An array containing the data for inserting the records.
     *                 The array should have the following structure:
     *                 - Each element of the array represents an account.
     *                 - Each account should be an associative array, where the keys represent the field names and
     *                   the values represent the corresponding values to be inserted.
     *                 - Only fields present in the 'self::$tabCritUI_CRPDetail' array will be considered for insertion.
     *                 - The field values should be provided as string values.
     *
     * @return int The number of inserted records.
     *
     * @throws \Exception If an error occurs during the insertion process.
     */
    static function ui_CRP_Detail(array $d): int
    {
        $set = [];

        foreach ($d as $account) {
            $row = [];

            foreach ($account as $field => $value) {
                if (isset(self::$tabCritUI_CRPDetail[$field])) {
                    $row[] = "'" . $value . "'";
                }
            }

            $set[] = implode(', ', $row);
        }

        if (!empty($set)) {
            $sql = "INSERT INTO crp_detail (CRP_NUM, STA_NUM, codePoste, Annuel, Montant, SAI_CLE, PrevTaux)
                    VALUES (" . implode('), (', $set) . ")";

            Database::query($sql);

            return Database::countRow();
        }

        return 0;
    }

    /**
     * Delete CRP_Detail records based on provided criteria.
     *
     * @param array $d An array containing the criteria for deleting the records.
     *                 The array should include the following keys:
     *                 - 'CRP_NUM' (optional): The CRP_NUM value for filtering the records.
     *                 - 'STA_NUM' (optional): The STA_NUM value for filtering the records.
     *                 - 'codePoste' (optional): The codePoste value for filtering the records.
     *                                          Can be an array of codePoste values or a single codePoste value.
     *
     * @return void
     *
     * @throws \Exception If 'CRP_NUM' and 'STA_NUM' keys are missing or empty in the provided criteria.
     */
    static function delete_CRP_Detail($d)
    {
        if (
            (!isset($d['CRP_NUM']) || !$d['CRP_NUM'])
            && (!isset($d['STA_NUM']) || !$d['STA_NUM'])
        ) {
            throw new Exception("delete_CRP_Detail > Variables obligatoires => CRP_NUM (" . $d['CRP_NUM'] . ") et STA_NUM (" . $d['STA_NUM'] . ")  ");
        }

        $where = '';

        if ($d['CRP_NUM']) {
            $where .= "AND CRP_NUM = '" . $d['CRP_NUM'] . "'";
        }

        if ($d['STA_NUM']) {
            $where .= "AND STA_NUM = '" . $d['STA_NUM'] . "'";
        }

        if (isset($d['codePoste']) && $d['codePoste']) {
            if (is_array($d['codePoste'])) {
                $where .= "AND codePoste IN (" . implode(', ', $d['codePoste']) . ")";
            } else {
                $where .= "AND codePoste = '" . $d['codePoste'] . "'";
            }
        }

        $sql = "DELETE FROM crp_detail WHERE 1 $where ";

        Database::query($sql);
    }
}
