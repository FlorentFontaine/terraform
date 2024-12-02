<?php

use Classes\DB\Database;
use Helpers\StringHelper;

require_once '../dbClasses/AccesDonnees.php';

class Lieu
{
    static function add($Prop): bool
    {
        if ($Prop["LIE_CODE"] && $Prop["LIE_NOM"]) {
            $Prop["LIE_NOM"] = strtoupper($Prop["LIE_NOM"]);
            $TabLie = StringHelper::cleanTab("LIE_", $Prop);

            if (dbAcces::AddLieu($TabLie, $Prop["UpdateLieu"])) {
                return true;
            }
        }
        return false;
    }

    static function Update($champ, $valeur, $LIE_NUM)
    {
        $sql = "update lieu set $champ = '$valeur' where LIE_NUM = '$LIE_NUM'";

        Database::query($sql);
    }
}
