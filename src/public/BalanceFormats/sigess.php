<?php
use Helpers\StringHelper;



$Lines = NULL;

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
		if(trim(str_replace("'","",$UneLigne[0])))
		{
			if(round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2))
			{
				$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2);
				//echo $t += $UneLigne["cumulbal"];echo "<br/>";
				$UneLigne[0] = str_replace("'","",$UneLigne[0]);
				$UneLigne[0] = $UneLigne[0] / 10;
				$Lines[$UneLigne[0]] = $UneLigne;
			}
		}
	}

}

//$MesCodes .= ")";

?>