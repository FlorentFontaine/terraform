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

	if($nbLigneFichier > 5)
	{
		$UneLigne = NULL;
		$UneLigne = explode("/t",$lecture);


		//var_dump($UneLigne);
                //die();
		//$MesCodes .= ",'".$UneLigne[0]."'";
              
		if(strlen($UneLigne[0]) == 165 )
		{
                    //$UneLigne[0] = str_replace(" ", "", $UneLigne[0]);
                    $Debit = substr(trim($UneLigne[0]),45,13);
                    //var_dump($Debit);
                    $Credit = substr(trim($UneLigne[0]),58,13);
                    //var_dump($Credit);
                    
                        
		
                    $UneLigne['debit'] = StringHelper::Texte2Nombre($Debit);
                    $UneLigne['credit'] = StringHelper::Texte2Nombre($Credit);
                    $UneLigne["cumulbal"] = (StringHelper::Texte2Nombre($Debit) -  StringHelper::Texte2Nombre($Credit))/100;
                    
                    $compte = substr($UneLigne[0],3,7);
                    
                    $UneLigne["1"] = substr($UneLigne[0],13,20);
                    $UneLigne["0"] = $compte;
                    //echo $t += $UneLigne["cumulbal"];echo "<br/>";

                    //$Test += $UneLigne["cumulbal"];
                     //echo substr($UneLigne[0],3,7)." --- ".$Debit."-".$Credit." --- ".$Test ."<br/>";
                    $Lines[$compte] = $UneLigne;
			
		}
	}

}
//var_dump($Lines);
//$MesCodes .= ")";

?>