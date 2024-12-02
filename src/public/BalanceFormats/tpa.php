<?php

//##### FORMAT D'IMPORT TPA ##############//
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

                        if(trim(substr($UneLigne[0],0,7))=="C000000"){
                            //$MesCodes .= ",'".$UneLigne[0]."'";
                            $UneLigne["2"] = substr($UneLigne[0],76,11);
                            $UneLigne["2"] = str_replace(' ','',$UneLigne["2"]);

                            $UneLigne["3"] = substr($UneLigne[0],89,11);
                            $UneLigne["3"] = str_replace(' ','',$UneLigne["3"]);

                            $UneLigne["cumulbal"] = $UneLigne[2] - $UneLigne[3];
                            $UneLigne["1"] = "Clients";
                            $UneLigne[0] = "4110000";
                             if($Lines[$UneLigne[0]])
                                $Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
                                else
                                $Lines[$UneLigne[0]] = $UneLigne;
                           



			//echo substr($UneLigne[0],0,7)." ".$UneLigne[2]." ".$UneLigne[3]." <br/>";
                        }elseif(trim(substr($UneLigne[0],0,1))=="F"){
                            //$MesCodes .= ",'".$UneLigne[0]."'";
                            $UneLigne["2"] = substr($UneLigne[0],76,11);
                            $UneLigne["2"] = str_replace(' ','',$UneLigne["2"]);

                            $UneLigne["3"] = substr($UneLigne[0],89,11);
                            $UneLigne["3"] = str_replace(' ','',$UneLigne["3"]);
                            $UneLigne["cumulbal"] = $UneLigne[2] - $UneLigne[3];
                            $UneLigne["1"] = "Fournisseurs";
                            $UneLigne[0] = "4011000";
                             if($Lines[$UneLigne[0]])
                                $Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
                                else
                                $Lines[$UneLigne[0]] = $UneLigne;



			//echo substr($UneLigne[0],0,7)." ".$UneLigne[2]." ".$UneLigne[3]." <br/>";
                        }

                        elseif(is_numeric(trim(substr($UneLigne[0],0,7)))){
                            //$MesCodes .= ",'".$UneLigne[0]."'";
                            $UneLigne["2"] = substr($UneLigne[0],76,11);
                            $UneLigne["2"] = str_replace(' ','',$UneLigne["2"]);

                            $UneLigne["3"] = substr($UneLigne[0],89,11);
                            $UneLigne["3"] = str_replace(' ','',$UneLigne["3"]);
                            $UneLigne["cumulbal"] = $UneLigne[2] - $UneLigne[3];
                            $UneLigne["1"] = trim(substr($UneLigne[0],13,22));
                            $UneLigne[0] = substr($UneLigne[0],0,7);
                             if($Lines[$UneLigne[0]])
                                $Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
                                else
                                $Lines[$UneLigne[0]] = $UneLigne;



			//echo substr($UneLigne[0],0,7)." ".$UneLigne[2]." ".$UneLigne[3]." <br/>";
                        }
		}
	}

}
//var_dump($Lines);

//$MesCodes .= ")";


?>