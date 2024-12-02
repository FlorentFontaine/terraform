
<center>

<table width="80%"><tr><td>

<table align="center" class="tabBalance"  bordercolordark="#000000" bordercolorlight="#000000" >



<tbody>
<?php

use htmlClasses\TableV2;

$NbCols = 2;

if(!$MoisVoulu)
    $NbCols--;

foreach($MesLignes as $codecompte => $UneLigne){

	
	//if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	//else $cssligne = 'bdligneimpaireTD';

        $cssligne1 = "";

	if(stristr($codecompte,"STOTAL") || stristr($codecompte,"Poste"))
            $cssligne1 = "bolder";
	elseif(stristr($codecompte,"ENCADRE"))
            $cssligne1 = "EnteteTab";
	elseif(!stristr($codecompte,"Total") && !stristr($codecompte,"VIDE"))
            $cssligne1 = "";
	else
            $cssligne1 = "";

	//$opt['debug_cletr'] = $codecompte;

        if(!$cssligne1){$cssli = "";}else{$cssli = array("class"=>$cssligne1);}
	echo table::getLine($UneLigne,$cssli,$NbCols,$opt);
}   ?>
</tbody>
</table>
</td></tr>
<tr><td align="left">


</td></tr></table>
</center>
