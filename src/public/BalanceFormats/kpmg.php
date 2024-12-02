<?php

//##### FORMAT D'IMPORT KPMG ##############//
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

                        if(strlen($UneLigne[0])>=7){
                            //$MesCodes .= ",'".$UneLigne[0]."'";
                            $UneLigne["2"] = str_replace(',','.',$UneLigne["2"]);
                            $UneLigne["3"] = str_replace(',','.',$UneLigne["3"]);
                            $UneLigne["cumulbal"] = $UneLigne[2] - $UneLigne[3];

                            if($Lines[substr($UneLigne[0],0,7)])
                                $Lines[substr($UneLigne[0],0,7)]["cumulbal"] += $UneLigne["cumulbal"];
                                else
                                $Lines[substr($UneLigne[0],0,7)] = $UneLigne;

                           $Lines[substr($UneLigne[0],0,7)][0] = substr($UneLigne[0],0,7);
                        }
		}
	}

}
//var_dump($Lines);

//$MesCodes .= ")";


?>