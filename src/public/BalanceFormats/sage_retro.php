<?php

//##### FORMAT D'IMPORT SAGE ##############//
//$dir : chemin du fichier

$FLines = file($dir,FILE_SKIP_EMPTY_LINES);
$Lines = NULL;
//$MesCodes = "(''";

foreach($FLines as $code => $UneFLigne)
{
	$MesLignes = NULL;
	$MesLignes = explode("\r",str_replace("\r\n","",$UneFLigne));
	
	foreach($MesLignes as $code => $Ligne)
	{
		if($Ligne)
		{
			$UneLigne = NULL;
			$UneLigne = explode("\t",str_replace("\r\n","",$Ligne));
			//var_dump($UneLigne);
	
			//$MesCodes .= ",'".$UneLigne[0]."'";
			$UneLigne["4"] = str_replace(',','.',$UneLigne["4"]);
			$UneLigne["5"] = str_replace(',','.',$UneLigne["5"]);
			$UneLigne["cumulbal"] = $UneLigne[4] - $UneLigne[5];
			
			$Lines[$UneLigne[0]] = $UneLigne;
		}
	}
	
}


//$MesCodes .= ")";


?>