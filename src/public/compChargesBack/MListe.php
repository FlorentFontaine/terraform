<?php


use htmlClasses\TableV2;if(!$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>COMPARATIF</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied


$AlerteProrata = "";

if(date("d",strtotime($_SESSION["station_DOS_DEBEX"])) > 1)
{
    //$AlerteProrata = "<b style='color:red'>(ATTENTION PRORATA PREMIER MOIS N-1)</b>";
}

$titleTable = strtoupper($Type);
if($mensuel) $titleTable .= " MENSUEL"; else $titleTable .= " CUMULE";



?>
    <?php if($cluster) $titleTable .= " CLUSTER"; ?>


<div class="titresection">
    <?php echo $titleTable; ?> <?php echo $AlerteProrata; ?> 
</div>


<script type="text/javascript">

ent_TableIds.push("tab_<?php echo $Type ?>1");
ent_TableIds.push("tab_<?php echo $Type ?>2");
ent_TableIds.push("tab_<?php echo $Type ?>3");

</script>


<?php

$Intitule = NULL;
if($Type == "Produits")
    $Intitule = "CA BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE";
compChargesProd::display_EnteteTab($titleTable,$Type,$cluster,$Intitule,$Imprimer);

//Définition des date au format jour-mois-année

if($Type == "Produits")
$NbCols = 7;
else
{
$NbCols = 7;
$opt["colorreverse"] = true;
}


$opt["colorsigne"] = array(6,7);

$LnInsere = true;

$LnNumber = 0;

$PRINTBREAKROW1 = 42;
$PRINTBREAKROW2 = 83;

$Intitule1 = "MARGE BOUTIQUE ET AUTRES PRESTATIONS DE SERVICE";
$Intitule2 = "PRODUITS MANDAT ET HORS MANDAT";

if($Type == "Charges")
{
    $PRINTBREAKROW1 = 0;
    $PRINTBREAKROW2 = "0";
}



foreach($MesLignes as $codecompte => $UneLigne)
{
	$LnNumber++;
    
	if($LnInsere)
	{
	    if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	    else $cssligne = 'bdligneimpaireTD';
	}

	if ($LnNumber == $PRINTBREAKROW1 )
	{
	    echo "</table>";
	    compChargesProd::display_EnteteTab($titleTable,$Type,$cluster,$Intitule1,$Imprimer);
	}
        
        if ($LnNumber == $PRINTBREAKROW2)
	{
	    echo "</table>";
	    compChargesProd::display_EnteteTab($titleTable,$Type,$cluster,$Intitule2,$Imprimer);
	}
	
	if(stristr($codecompte,"MARGESTOTALONFR"))
	{
	    $cssligne1 = $cssligne;
	}
	elseif(stristr($codecompte,"STOTAL"))
	{
		//echo $codecompte."<br/>";
		$cssligne1 = "lnstotal";
	}
	elseif(stristr($codecompte,"TOTAL"))
	{
		//echo $codecompte."<br/>";
		$cssligne1 = "lntotal";
	}
	elseif(stristr($codecompte,"TITRE"))
		$cssligne1 = "EnteteTab";
	else
		$cssligne1 = $cssligne;
	//$opt['debug_cletr'] = $codecompte;
	
	echo $LnInsere = table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);
	
	
	
	
}

/*if($Type != "Produits" && !$Projection)
{
	$MesLigneHeure = compChargesProd::getTabHeures($MoisVoulu,$cumul);
	
	
	foreach($MesLigneHeure as $codecompte => $UneLigne)
	{
		if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
		else $cssligne = 'bdligneimpaireTD';	
	
		if(stristr($codecompte,"STOTAL"))
		{
			//echo $codecompte."<br/>";
			$cssligne1 = "lnstotal";
		}
		elseif(stristr($codecompte,"TOTAL"))
		{
			//echo $codecompte."<br/>";
			$cssligne1 = "lntotal";
		}
		elseif(stristr($codecompte,"TITRE"))
			$cssligne1 = "EnteteTab";
		else
			$cssligne1 = $cssligne;
		//$opt['debug_cletr'] = $codecompte;
		echo table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);
			if($codecompte == "BIGTOTAL")
			$opt["colorreverse"] = false;
	}
}*/


?>

<?php
/*
if($Type == "Charges")
    include '../shell_Prev/ONFR3Charges.php';
else
    include '../shell_Prev/ONFR3Produits.php';
*/
?>
</tbody>
</table>



<?php


if(!$EntetePiedFalse)
{
?>
<?php
include("../include/pied.inc.php");
?>




</body>
</html>
<?php
}
 ?>