<?php
use Helpers\StringHelper;

//##### FORMAT D'IMPORT SAGIREC ##############//
//$dir : chemin du fichier

//$FLines = file($dir,FILE_SKIP_EMPTY_LINES);
$Lines = NULL;
//$MesCodes = "(''";
$ouvre=fopen($dir,"r");  // ouverture du fichier
while (!feof ($ouvre))          // tant que pas en fin de fichier
	{

		$lecture = fgets($ouvre); // stockage dans $lecture
		$FLines = explode(chr(13),$lecture);
		
		
		foreach($FLines as $code => $UneFLigne)
		{
			
			$UneLigne = NULL;
			$UneLigne = explode(";",$UneFLigne);
			//var_dump($UneLigne);die();
			//$MesCodes .= ",'".$UneLigne[0]."'";
                        $UneLigne = str_replace('"','',$UneLigne);
			if(trim($UneLigne[0]) && is_numeric($UneLigne[0]))
			{
                                $UneLigne[0] = substr($UneLigne[0],0,7);
                                
                                $UneLigne[3] = trim($UneLigne[2]);
                                $UneLigne[2] = trim($UneLigne[1]);
                                $UneLigne[1] = "";
				$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2);
                                //echo substr($UneLigne[0],0,7)." "."<br/>";
				//echo $t += $UneLigne["cumulbal"];echo "<br/>";
				$Lines[$UneLigne[0]] = $UneLigne;
			}
		}
	}

//$MesCodes .= ")";
//var_dump($Lines);

?>