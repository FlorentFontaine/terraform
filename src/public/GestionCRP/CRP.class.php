<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once __DIR__ . '/../../Init/bootstrap.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

class CRP
{
    /**
     * Generate an HTML form for creating or editing a CRP.
     *
     * @param array $d An optional array of data for pre-populating the form fields.
     * @return string The HTML code for the form.
     */
    static function HTML_FormCRP(array $d = []): string
    {
        $disabled = $title = $CRP_FIN = $CRP_NB_MOIS = '';

        if (isset($d["CRP_NUM"]) && $d["CRP_NUM"]) {
            //Récupération du dernier CRP pour cette SARL
            $param = array(
                "tabCriteres" => array(
                    "STA_NUM" => $_SESSION["station_STA_NUM"],
                    "CRP_NUM" => $d["CRP_NUM"]
                )

            );
            $CRP = db_CRP::select_CRP($param);
            $CRP = $CRP[$d["CRP_NUM"]];

            $CRP_DBT = StringHelper::MySql2DateFr($CRP["CRP_DBT"]);
            $CRP_FIN = StringHelper::MySql2DateFr($CRP["CRP_FIN"]);
            $CRP_NB_MOIS = $CRP["CRP_NB_MOIS"];

            $disabled = "disabled='disabled'";
            $title = 'title="Impossible de modifier la date de d&eacute;but"';
        } else {
            //Récupération du dernier CRP pour cette SARL
            $param = array(
                "tabCriteres" => array("STA_NUM" => $_SESSION["station_STA_NUM"]),
                "triRequete" => " order by CRP_FIN DESC LIMIT 0,1 "
            );
            $LastCRP = db_CRP::select_CRP($param);
            $array_keys = array_keys($LastCRP);
            $key = $array_keys[0];
            $LastCRP = $LastCRP[$key];

            $CRP_DBT = StringHelper::DatePlus($LastCRP["CRP_FIN"], array("dateformat" => "d/m/Y", "joursplus" => 1));
        }

        $r = '<table border="0" style="">';

        $r .= '<tr >';
            $r .= '<td align="left" height="30">';
                $r .= '<b>Date de d&eacute;but : </b>';
            $r .= '</td>';
            $r .= '<td>';
                if ($disabled != '') {
                    $r .= ' <input type="text" class="gapiareamontant datepicked" ' . $title . ' ' . $disabled . ' id="CRP_DBT"  style="width:75px" value="' . $CRP_DBT . '"/>';
                    $r .= ' <input type="hidden" name="CRP_DBT" value="' . $CRP_DBT . '"/>';
                } else {
                    $r .= ' <input type="text" class="gapiareamontant datepicked" id="CRP_DBT" name="CRP_DBT" style="width:75px" value="' . $CRP_DBT . '"/>';
                }
            $r .= '</td>';
        $r .= '</tr>';

        $r .= '<tr >';
            $r .= '<td  align="left" height="30">';
                $r .= '<b>Date de fin : </b>';
            $r .= '</td>';
            $r .= '<td>';
                $r .= '<input type="text" class="gapiareamontant datepicked" id="CRP_FIN" name="CRP_FIN" style="width:75px" value="' . $CRP_FIN . '"/>';
            $r .= '</td>';
        $r .= '</tr>';

        $r .= '<tr >';
            $r .= '<td  align="left" height="30">';
                $r .= '<b>Dur&eacute;e des valeurs : </b>';
            $r .= '</td>';
            $r .= '<td>';
                $r .= '<input type="text" class="gapiareamontant" id="CRP_NB_MOIS" name="CRP_NB_MOIS" style="width:75px" value="' . $CRP_NB_MOIS . '"/>';
                $r .= ' <input type="hidden"  id="STA_NUM" name="STA_NUM" value="' . $_SESSION["station_STA_NUM"] . '" />';
                $r .= ' <input type="hidden"  id="CRP_NUM" name="CRP_NUM" value="' . $d["CRP_NUM"] . '" />';
            $r .= '</td>';
        $r .= '</tr>';

        $r .= '</table>';

        return $r;
    }

    /**
     * Generates an HTML form for copying a previous CRP onto a new CRP
     *
     * @param array $d - An array of data
     * @return string - The HTML form as a string
     */
    static function HTML_FormCopie_CRP(array $d = []): string
    {
        if (!isset($d["CRP_NUM"]) || !$d["CRP_NUM"]) {
            return '';
        }

        $r = "Vous &ecirc;tes sur le point de copier le CRP pr&eacute;c&eacute;dent sur ce CRP.<br/>Quelles donn&eacute;es souhaitez-vous copier ? ";

        $r .= '<table border="0" style="">';

        $r .= '<tr >';
            $r .= '<td>';
                $r .= ' <input type="checkbox" name="cle_checked" checked="checked" value="1" />';
            $r .= '</td>';
            $r .= '<td align="left" height="30">';
                $r .= '<b>Les cl&eacute;s </b>';
            $r .= '</td>';
        $r .= '</tr>';

        $r .= '<tr >';
            $r .= '<td>';
                $r .= '<input type="checkbox"  name="valeur_checked" checked="checked" value="1" />';
            $r .= '</td>';
            $r .= '<td  align="left" height="30">';
             $r .= '<b>Les valeurs et taux de marge</b>';
            $r .= '</td>';
        $r .= '</tr>';

        $r .= '</table>';
        $r .= ' <input type="hidden"  id="STA_NUM" name="STA_NUM" value="' . $_SESSION["station_STA_NUM"] . '" />';
        $r .= ' <input type="hidden"  id="CRP_NUM" name="CRP_NUM" value="' . $d["CRP_NUM"] . '" />';

        return $r;
    }

    /**
     * Retrieves the list of CRP for a certain SARL.
     *
     * @return string The HTML string representing the table of CRP or a message indicating the absence of CRP.
     */
    static function get_TabCRP(): string
    {
        //Récupération de la liste des CRP pour cette SARL
        $param = array(
            "tabCriteres" => array("STA_NUM" => $_SESSION["station_STA_NUM"]),
            "triRequete" => " order by CRP_DBT DESC "
        );
        $ListeCRP = db_CRP::select_CRP($param);

        $r = "<table style='width:422px;border:1px solid grey'>";
        $r .= '<tr>';
            $r .= '<td class="EnteteTab TitreTable" colspan="5" style="text-align:center;font-weight:bold;border:none">';
                $r .= 'LISTE DES CRP';
            $r .= '</td>';
        $r .= '</tr>';

        if ($ListeCRP) {
            $r .= '<tr class="EnteteTab ">';
                $r .= "<td>D&eacute;but - Fin</td>";
                $r .= "<td></td>";
            $r .= "</tr>";

            $prem = true;
            foreach ($ListeCRP as $CRP_NUM => $val) {
                $r .= '<tr >';
                    $r .= "<td style='text-align: center; padding: 4px;'>";
                        $r .= "<a style='text-decoration:none;font-weight: bolder;' href='index.php?page=form&CRP_NUM=" . $CRP_NUM . "'>";
                            $r .= StringHelper::MySql2DateFr($val["CRP_DBT"]) . " - " . StringHelper::MySql2DateFr($val["CRP_FIN"]);
                        $r .= "</a>";
                    $r .= "</td>";
                    $r .= "<td style='text-align: center; padding: 4px;'>";
                        if ($prem) {
                            $r .= "<a style='text-decoration:none;' id='modif_" . $CRP_NUM . "' class='modif_date_CRP' href='#'>Modifier les dates</a>";
                        }
                    $r .= "</td>";
                $r .= "</tr>";
                $prem = false;
            }
        } else {
            $r .= '<tr><td class=" " colspan="5" style="text-align:center;border:none">Aucun CRP</td></tr>';
        }

        $r .= "</table>";

        return $r;
    }

    /**
     * Verifies the provided data for creating or updating a CRP.
     *
     * @param array $d The data to be verified, including the CRP start date, end date, and duration values.
     * @param string $e A string variable to store any error messages generated during verification.
     *
     * @return bool Returns true if the data passes verification, and false otherwise.
     */
    static function verifier_donnees(array $d = [], string &$e = ''): bool
    {
        $verificationOK = true;

        if (!isset($d["CRP_DBT"]) || !$d["CRP_DBT"]) {
            $e .= "La date de d&eacute;but n'est pas renseign&eacute;e.<br/><br/>";
            $verificationOK = false;
        }

        if (!isset($d["CRP_FIN"]) || !$d["CRP_FIN"]) {
            $e .= "La date de fin n'est pas renseign&eacute;e.<br/><br/>";
            $verificationOK = false;
        }

        if (!isset($d["CRP_NB_MOIS"]) || !$d["CRP_NB_MOIS"]) {
            $e .= "La dur&eacute;e des valeurs n'est pas renseign&eacute;e.";
            $verificationOK = false;
        }

        if ($e != '') {
            return $verificationOK;
        }

        $CRP_DBT = StringHelper::DateFr2MySql($d["CRP_DBT"]);
        $CRP_FIN = StringHelper::DateFr2MySql($d["CRP_FIN"]);
        $CRP_NUM = $d["CRP_NUM"];

        if (!$CRP_NUM) {
            if ($CRP_DBT > $CRP_FIN) {
                $e .= "La date de d&eacute;but est sup&eacute;rieure &agrave; la date de fin du CRP.<br/><br/><br/><br/>";
                $verificationOK = false;
            }

            if ($CRP_DBT == $CRP_FIN) {
                $e .= "La date de d&eacute;but est &eacute;gale &agrave; la date de fin du CRP.<br/><br/><br/><br/>";
                $verificationOK = false;
            }

            $param = array(
                "tabCriteres" => array(
                    "CRP_DBT" => $CRP_DBT,
                    "CRP_FIN" => $CRP_DBT,
                    "STA_NUM" => $d["STA_NUM"]
                ),
                "tabOP" => array(
                    "CRP_DBT" => "<=",
                    "CRP_FIN" => ">="
                )
            );
            $TabCRP_Dbt = db_CRP::select_CRP($param);

            $param["tabCriteres"]["CRP_DBT"] = $CRP_FIN;
            $param["tabCriteres"]["CRP_FIN"] = $CRP_FIN;

            $TabCRP_Fin = db_CRP::select_CRP($param);

            $param["tabCriteres"]["CRP_DBT"] = $CRP_DBT;
            $param["tabCriteres"]["CRP_FIN"] = $CRP_FIN;

            $param["tabOP"]["CRP_DBT"] = "<=";
            $param["tabOP"]["CRP_FIN"] = ">=";

            $TabCRP_entier = db_CRP::select_CRP($param);

            if ($TabCRP_Dbt || $TabCRP_Fin || $TabCRP_entier) {
                $e .= "Les dates de ce nouveau CRP chevauchent un CRP existant.<br/><br/>";
                $verificationOK = false;
            }
        }

        return $verificationOK;
    }

    /**
     * Formats the given data by converting the date fields from French format to MySQL format.
     *
     * @param array $d The data array containing the CRP_DBT and CRP_FIN fields in French format.
     * @return array The formatted data array with the CRP_DBT and CRP_FIN fields in MySQL format.
     */
    static function formater_donnees(array $d): array
    {
        $d["CRP_DBT"] = StringHelper::DateFr2MySql($d["CRP_DBT"]);
        $d["CRP_FIN"] = StringHelper::DateFr2MySql($d["CRP_FIN"]);

        return $d;
    }

    /**
     * Saves the CRP data into the database and returns the generated CRP number.
     *
     * @param array $d The CRP data to be saved.
     * @param string $e The error message in case of validation failure.
     * @return int The generated CRP number if saved successfully, otherwise 0.
     */
    static function enregistrer_CRP(array $d, string &$e = ''): int
    {
        self::verifier_donnees($d, $e);

        //Aucune erreur
        if ($e == '') {
            $d = self::formater_donnees($d);
            $CRP_NUM = db_CRP::ui_CRP($d);
        }

        return $CRP_NUM ?? 0;
    }

    /**
     * Deletes a CRP from the database.
     *
     * @param int $CRP_NUM The unique identifier of the CRP to be deleted.
     *
     * @return void
     */
    static function supprimer_CRP(int $CRP_NUM)
    {
        db_CRP::delete_CRP($CRP_NUM);
    }
}

/**
 * Classe d'accès aux données de la table CRP
 */
class db_CRP extends dbAcces
{
    /**
     * Décrire ici les noms des champs pour Insert / Update
     */
    private static array $tabCritUI_CRP = [
        "STA_NUM" => 1,
        "CRP_DBT" => 1,
        "CRP_FIN" => 1,
        "CRP_NB_MOIS" => 1
    ];

    /**
     * Fonction de sélection des lignes dans la table CRP en fonction de critères donnés
     *
     * @param array $d - Un tableau contenant les paramètres de la requête
     *     - tabCriteres : un tableau associatif contenant les critères de sélection, où la clé est le nom du champ et la valeur est la valeur recherchée
     *     - tabOP : un tableau associatif contenant les opérateurs de comparaison des critères, où la clé correspond au nom du champ
     *     - $join : une chaîne représentant la clause JOIN de la requête SQL
     *     - $triRequete : une chaîne représentant la clause ORDER BY de la requête SQL
     * @return array - Un tableau associatif contenant les lignes sélectionnées, où la clé est la valeur de "CRP_NUM" et la valeur est un tableau contenant les données de la ligne
     */
    static function select_CRP(array $d = []): array
    {
        $tabRetour = [];

        if (!isset($d['tabCriteres']) || !$d['tabCriteres']) {
            return $tabRetour;
        }
        $d['$join'] ??= '';
        $d['$triRequete'] ??= '';

        $where = "";

        //Boucle de construction du where
        foreach ($d['tabCriteres'] as $nomChamp => $valeur) {
            if (!is_array($valeur)) {
                if (!isset($d['tabOP'][$nomChamp]) || !$d['tabOP'][$nomChamp]) {
                    $operation = " = ";
                } else {
                    $operation = $d['tabOP'][$nomChamp];
                }

                $where .= " And " . $nomChamp . " " . $operation . " \"" . $valeur . "\" ";
            } elseif (isset($valeur["whereperso"])) {
                $where .= " AND ( ";
                $where .= $nomChamp . $valeur["whereperso"];
                $where .= " ) ";
            }
        }

        //CONSTRUCTION DE LA REQUETE SQL
        $sql = "SELECT * FROM CRP
            " . $d['$join'] . "
            WHERE 1 " . $where . " " . $d['$triRequete'] . " ";

        Database::query($sql);
        while ($ligne = Database::fetchArray()) {
            $tabRetour[$ligne["CRP_NUM"]] = $ligne;
        }

        return $tabRetour;
    }

    /**
     * Updates or inserts a record into the CRP table based on the provided data.
     *
     * @param array $d An associative array containing the data for the record. It should have a key "CRP_NUM" to identify the record.
     *
     * @return int Returns the CRP number of the updated or inserted record if successful, otherwise returns 0.
     */
    static function ui_CRP(array $d): int
    {
        if (!isset($d["CRP_NUM"]) || !$d["CRP_NUM"]) {
            $sql = "INSERT INTO CRP (CRP_NUM) VALUES (NULL)";

            Database::query($sql);
            $d["CRP_NUM"] = Database::lastPk();
        }

        $CRP_NUM = $d["CRP_NUM"];

        $set = [];
        foreach ($d as $nomChamp => $valeur) {
            if (isset(self::$tabCritUI_CRP[$nomChamp])) {
                $set[] = " " . $nomChamp . " = \"" . $valeur . "\" ";
            }
        }

        if (!empty($set)) {
            $sql = "UPDATE CRP SET " . implode(' ,', $set) . " WHERE CRP_NUM = " . $CRP_NUM;
            $res = Database::query($sql);

            return $res ? $CRP_NUM : 0;
        }

        return 0;
    }


    /**
     * Deletes a record from the GestionCRP table based on the provided CRP number.
     *
     * @param int $CRP_NUM The CRP number of the record to be deleted.
     *
     * @return void
     */
    static function delete_CRP(int $CRP_NUM)
    {
        $sql = "DELETE FROM GestionCRP WHERE CRP_NUM = " . $CRP_NUM;

        self::$db->sql_Query($sql);
    }
}
