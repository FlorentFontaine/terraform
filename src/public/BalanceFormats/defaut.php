<?php
use Helpers\StringHelper;

//##### FORMAT D'IMPORT PAR DEFAUT ##############//
//$dir : chemin du fichier

//$FLines = file($dir,FILE_SKIP_EMPTY_LINES);
$Lines = NULL;
//$MesCodes = "(''";

//error_reporting(E_ALL);
$ouvre=fopen($dir,"r");  // ouverture du fichier
while (!feof ($ouvre))          // tant que pas en fin de fichier

{

    $lecture = fgets($ouvre); // stockage dans $lecture
    $FLines = explode(chr(13),$lecture);


    foreach($FLines as $code => $UneFLigne)
    {

	$UneLigne = NULL;
	$UneLigne = explode("\t",$UneFLigne);

	if(trim($UneLigne[0]) && is_numeric($UneLigne[0]))
	{
	    $LongueurCompte = strlen($UneLigne[0]);
	    $DernCaractere = substr($UneLigne[0],$LongueurCompte-2,1);


	    while($LongueurCompte > 7 && $DernCaractere == "0")
	    {
		$UneLigne[0] = $UneLigne[0] / 10;
		$LongueurCompte = strlen($UneLigne[0]);
	    }

	    $LongueurCompte = strlen($UneLigne[0]);


	    while(strlen($UneLigne[0]) < 7)
		$UneLigne[0] = $UneLigne[0]."0";


	    $UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2);

	    $Lines[$UneLigne[0]] = $UneLigne;


	}
    }
}

//$MesCodes .= ")";


?>