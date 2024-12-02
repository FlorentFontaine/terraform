<?php

use Classes\DB\Database;

class Filtres
{

    public static function cdr($params): string
    {
        return self::generateSelect('cdr', 'Responsable r&eacute;seau', $params);
    }

    public static function cds($params): string
    {
        return self::generateSelect('cds', 'Chef de secteur', $params);
    }

    public static function lieu($params): string
    {
        return self::generateSelect('lieu', 'PDV', $params);
    }

    public static function cabinet($params): string
    {
        return self::generateSelect('cabinet', 'Cabinet comptable', $params);
    }

    public static function initFieldsValue(array $expectedFields)
    {
        foreach ($expectedFields as $sessionFieldName) {
            $fieldName = str_replace('sarl_liste_', '', $sessionFieldName);

            // Si on a des données en POST, on les met dans SESSION
            if (isset($_POST[$fieldName]) && $_POST[$fieldName]) {
                $_SESSION[$sessionFieldName] = $_POST[$fieldName];
            }

            // Si on a des données en SESSION (en venant d'une autre page par exemple), on les met dans POST
            if (isset($_SESSION[$sessionFieldName]) && $_SESSION[$sessionFieldName]) {
                $_POST[$fieldName] = $_SESSION[$sessionFieldName];
            }

            // Si on a des données en ALL, on remet à zéro le filtre dans POST et dans SESSION
            if (isset($_POST[$fieldName]) && $_POST[$fieldName] == "-1") {
                $_POST[$fieldName] = "";
                $_SESSION[$sessionFieldName] = "";
            }
        }
    }

    private static function generateSelect($type, $defaultValue, $params): string
    {
        $return = self::startSelect($params);

        if (!isset($params['allowAllOption']) || $params['allowAllOption']) {
            $return .= self::getDefaultOption($defaultValue);
        }

        $return .= self::loadOption($type, $params['value']);
        $return .= self::endSelect();

        return $return;
    }

    private static function startSelect($params): string
    {
        $name = $params['name'];
        $id = $params['id'] ?? $name;
        $style = isset($params['style']) ? ' style="' . $params['style'] . '" ' : '';
        $onchange = isset($params['onchange']) ? ' onchange="' . $params['onchange'] . '" ' : '';

        return '<div class="select"><select name="' . $name . '" id="' . $id . '" ' . $style . ' ' . $onchange . '>';
    }

    private static function endSelect(): string
    {
        return '</select></div>';
    }

    private static function getDefaultOption($value): string
    {
        return '<option value="-1">' . $value . '</option>';
    }

    private static function loadOption($type, $selectedValue): string
    {
        $return = '';

        list($sql, $key) = self::getConfigOption($type);

        if ($sql === '') {
            return $return;
        }

        Database::query($sql);

        while ($Ln = Database::fetchArray()) {
            $selected = "";

            if ($selectedValue == $Ln[$key]) {
                $selected = " selected='selected' ";
            }

            $valAff = trim(stripslashes(htmlentities($Ln["Libelle"], null, 'ISO-8859-1')));

            $return .= "<option value='" . $Ln[$key] . "' $selected>" . $valAff . "</option>";
        }

        return $return;
    }

    private static function getConfigOption($type): array
    {
        global $User;
        $sql = $key = '';

        switch ($type) {
            case 'cdr':
                $sql = "select codeChefRegion, CONCAT(Nom, ' ', Prenom) AS Libelle  from chefRegion order by Nom";
                $key = "codeChefRegion";
                break;

            case 'cds':
                $sql = "select codeChefSecteur, CONCAT(Nom, ' ', Prenom) AS Libelle  from chefSecteur order by Nom";
                $key = "codeChefSecteur";
                break;

            case 'lieu':
                $sql = "select LIE_NUM, CONCAT(LIE_CODE, ' - ', LIE_NOM) AS Libelle from lieu order by LIE_CODE";
                $key = "LIE_NUM";
                break;

            case 'cabinet':
                $where = "";
                $join = $User->JoinRequired("cabinet");

                if ($User->Type == "Secteur") {
                    $join .= " join comptable on cabinet.CAB_NUM = comptable.CAB_NUM
                               join stationcc on stationcc.CC_NUM = comptable.CC_NUM
                               join station on station.STA_NUM = stationcc.STA_NUM";
                    $where = " and station.codeChefSecteur = " . $User->Var["codeChefSecteur"];
                }

                $sql = "select CAB_NUM, CAB_NOM AS Libelle from cabinet $join  where 1 $where order by CAB_NOM";
                $key = "CAB_NUM";
                break;

            default:
                break;
        }

        return [$sql, $key];
    }
}
