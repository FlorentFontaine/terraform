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
			//$UneLigne["2"] = str_replace(',','.',$UneLigne["2"]);
                        if(is_numeric($UneLigne[0])){

                            $UneLigne[0] = substr($UneLigne[0],0,7);
                            $UneLigne[4] = str_replace(',','.',$UneLigne[4]);
                            $UneLigne["cumulbal"] = round($UneLigne[4],2);
                            //$somme += round($UneLigne[4],2);
                            //echo round($UneLigne[4],2). " + " .$somme ."<br/>";
                            if($Lines[$UneLigne[0]])
                                $Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
                            else
                                $Lines[$UneLigne[0]] = $UneLigne;
                        }
		}
	}
	
}
//var_dump($Lines);

//$MesCodes .= ")";


?>