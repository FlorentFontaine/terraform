<?php
use Helpers\StringHelper;



$Lines = NULL;

$ouvre=fopen($dir,"r");  // ouverture du fichier

$nbLigneFichier = 0;

while (!feof ($ouvre))          // tant que pas en fin de fichier
{
	

	$lecture = fgets($ouvre); // stockage dans $lecture


	$UneLigne = NULL;
	$UneLigne = explode("\t",$lecture);


	//var_dump($UneLigne);die();
	//$MesCodes .= ",'".$UneLigne[0]."'";
	if(trim($UneLigne[0]) && $UneLigne[0] > 999)
	{
		if(round(StringHelper::Texte2Nombre($UneLigne[2]),2))
		{
			//echo "".(StringHelper::Texte2Nombre($UneLigne[2]) - StringHelper::Texte2Nombre($UneLigne[3]))."\n";

			$LongueurCompte = strlen($UneLigne[0]);
			$DernCaractere = substr($UneLigne[0],$LongueurCompte-2,1);


			while($LongueurCompte > 7 && $DernCaractere == "0")
			{
			    $UneLigne[0] = $UneLigne[0] / 10;
			    $LongueurCompte = strlen($UneLigne[0]);
			}

			$LongueurCompte = strlen($UneLigne[0]);

			while($LongueurCompte < 7)
				$UneLigne[0] = $UneLigne[0]."0";


			$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2]),2);

			$Lines[$UneLigne[0]] = $UneLigne;
		}
	}


}


//$MesCodes .= ")";

?>