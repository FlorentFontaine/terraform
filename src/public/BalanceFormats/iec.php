<?php
use Helpers\StringHelper;

//##### FORMAT D'IMPORT PAR DEFAUT ##############//
//$dir : chemin du fichier

//$FLines = file($dir,FILE_SKIP_EMPTY_LINES);
$Lines = NULL;
//header('Content-Type: text/html; charset=iso-8859-1');

//$MesCodes = "(''";
$ouvre=fopen($dir,"r");  // ouverture du fichier

$nbLigneFichier = 0;

while (!feof ($ouvre))          // tant que pas en fin de fichier
{
	$nbLigneFichier++;
	
	$lecture = fgets($ouvre); // stockage dans $lecture
	
	if($nbLigneFichier > 1)
	{
		$UneLigne = NULL;
		$UneLigne = explode("\t",$lecture);
		
		
		//var_dump($UneLigne);die();
		//$MesCodes .= ",'".$UneLigne[0]."'";
		if(trim($UneLigne[0]) && $UneLigne[0] > 999999)
		{
			if(round(StringHelper::Texte2Nombre($UneLigne[2]) - StringHelper::Texte2Nombre($UneLigne[3]),2))
			{
				
				while(strlen($UneLigne[0]) > 7)
					$UneLigne[0] = $UneLigne[0] / 10;
					
				while(strlen($UneLigne[0]) < 7)
					$UneLigne[0] = $UneLigne[0]."0";
				
				
				$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2]) - StringHelper::Texte2Nombre($UneLigne[3]),2);
				//echo $t += $UneLigne["cumulbal"];echo "<br/>";
				
				$Lines[$UneLigne[0].""] = $UneLigne;
			}
		}
	}
	
}

//$MesCodes .= ")";

?>