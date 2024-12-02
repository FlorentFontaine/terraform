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

                /*foreach($UneLigne as &$Val)
                {
                    $Val = str_replace("\n","",$Val);
                }*/

		
		//var_dump($UneLigne);die();
		//$MesCodes .= ",'".$UneLigne[0]."'";
		if(trim(str_replace("'","",$UneLigne[0])))
		{

                    //echo round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2)."<br/>";
			if(round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2))
			{
				$UneLigne["cumulbal"] = round(StringHelper::Texte2Nombre($UneLigne[2])-StringHelper::Texte2Nombre($UneLigne[3]),2);
				//echo $t += (int) round($UneLigne["cumulbal"],2);echo "<br/>";
				$UneLigne[0] = str_replace("'","",$UneLigne[0]);
				$UneLigne[0] = $UneLigne[0] / 10;

                                if($Lines[$UneLigne[0]])
                                {
                                    $Lines[$UneLigne[0]]["cumulbal"] += $UneLigne["cumulbal"];
                                    $Lines[$UneLigne[0]][2] += $UneLigne[2];
                                    $Lines[$UneLigne[0]][3] += $UneLigne[3];
                                    $Lines[$UneLigne[0]][4] += $UneLigne[4];
                                }
                                else
                                    $Lines[$UneLigne[0]] = $UneLigne;
			}
		}
	}
	
}



/*
$Montest = NULL;
                        foreach ($Lines as $cpt => $LigneTab)
                        {
                            echo "<br/>".$LigneTab["cumulbal"];
                            $Montest[] = $LigneTab["cumulbal"];
                        }

                        var_dump(array_sum($Montest));die();

die();*/
//$MesCodes .= ")";

?>