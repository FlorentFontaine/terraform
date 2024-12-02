<?php
use Helpers\StringHelper;

$Lines = NULL;

$ouvre=fopen($dir,"r");  // ouverture du fichier
while (!feof ($ouvre))          // tant que pas en fin de fichier
{

    $lecture = fgets($ouvre); // stockage dans $lecture
    $FLines = explode("\n\r",$lecture);

    foreach($FLines as $code => $UneFLigne)
    {
	$UneLigne = NULL;
	$UneLigne = explode(";",$UneFLigne);

	$UneLigne[0] = trim(substr($UneLigne[0], 0,7));

	if(preg_match("/^401[a-z-A-Z]/", $UneLigne[0]))
	{
	    $UneLigne[0] = "4010000";
	}

	if(preg_match("/^411[a-z-A-Z]/", $UneLigne[0]))
	{
	    $UneLigne[0] = "4110000";
	}

	if(is_numeric($UneLigne[0]))
	{
	    unset($UneLigne[12]);
	    $UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[4])-StringHelper::Texte2Nombre($UneLigne[5]),2);
	    
	    if($Lines[$UneLigne[0]])
	    {
		$Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
	    }
	    else
		$Lines[$UneLigne[0]] = $UneLigne;
	}
    }
}


?>