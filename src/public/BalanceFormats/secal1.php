<?php
use Helpers\StringHelper;

//##### FORMAT D'IMPORT PAR DEFAUT ##############//
//$dir : chemin du fichier

//$FLines = file($dir,FILE_SKIP_EMPTY_LINES);
$Lines = NULL;
//header('Content-Type: text/html; charset=iso-8859-1');

//$MesCodes = "(''";
$ouvre=fopen($dir,"r");  // ouverture du fichier

while (!feof ($ouvre))          // tant que pas en fin de fichier
{

	$lecture = fgets($ouvre); // stockage dans $lecture
	
	$UneLigne = NULL;
	$UneLigne = explode("\t",$lecture);

	if(trim($UneLigne[0]) && is_numeric($UneLigne[0]))
	{
		if(round(StringHelper::Texte2Nombre($UneLigne[4]) - StringHelper::Texte2Nombre($UneLigne[5]),2))
		{
			
			while(strlen($UneLigne[0]) > 7)
				$UneLigne[0] = $UneLigne[0] / 10;
				
			while(strlen($UneLigne[0]) < 7)
				$UneLigne[0] = $UneLigne[0]."0";
			
			
			$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[4]) - StringHelper::Texte2Nombre($UneLigne[5]),2);
			//echo $t += $UneLigne["cumulbal"];echo "<br/>";
			
			$Lines[$UneLigne[0].""] = $UneLigne;
		}
	}

}

//$MesCodes .= ")";

?>