<?php

use Helpers\StringHelper;

//##### FORMAT D'IMPORT PAR DEFAUT ##############//

// $dir : chemin du fichier
/** @var string $dir */

$Lines = [];

// ouverture du fichier
$ouvre = fopen($dir, "r");

// tant que pas en fin de fichier
while (!feof($ouvre)) {
    // stockage dans $lecture
    $lecture = fgets($ouvre);
    $UneLigne = explode("\t", $lecture);

    if (trim($UneLigne[0]) && is_numeric($UneLigne[0])) {
        if (round(StringHelper::Texte2Nombre($UneLigne[4]) - StringHelper::Texte2Nombre($UneLigne[5]), 2)) {
            $LongueurCompte = strlen($UneLigne[0]);
            $DernCaractere = substr($UneLigne[0], $LongueurCompte - 2, 1);

            while ($LongueurCompte > 7 && $DernCaractere == "0") {
                $UneLigne[0] = $UneLigne[0] / 10;
                $LongueurCompte = strlen($UneLigne[0]);
            }

            $LongueurCompte = strlen($UneLigne[0]);

            while (strlen($UneLigne[0]) < 7) {
                $UneLigne[0] = $UneLigne[0] . "0";
            }

            $UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[4]) - StringHelper::Texte2Nombre($UneLigne[5]), 2);

            $Lines[$UneLigne[0] . ""] = $UneLigne;
        }
    }
}
