<?php
use Helpers\StringHelper;
use htmlClasses\TableV2;
if(!$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Synthese</title>
<link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied
$TitleTable = "SYNTHESE";
if($cluster) $TitleTable .= " CLUSTER";
            $TitleTable .= " - ".StringHelper::DateComplete(str_replace("-00","-01",$_SESSION["MoisHisto"]))." - ".$_SESSION["station_BALI_TYPE_exp"];
?>
<!--<div class="titresection"><?php echo $TitleTable; ?></div>-->

<script type="text/javascript"> ent_TableIds.push("tab_synthese");   </script>

<table id="tab_synthese" dir="IMP_PDF;TITLETABLE:SYNTHESE;FREEZEPLAN:B5;HEIGHT:27;FITHEIGHT:1;ORIENTATION:LANDSCAPE;" style="width:0px" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000" align="center" id="tab_<?php echo $Type ?>">
<thead>
<?php if($cluster){ ?>
<tr>
    <td class="EnteteTab">
	Stations du CLUSTER :
    </td>
    <td colspan="7" style="padding: 5px;white-space: normal;width: 1px" align="center">
	<?php

	$MesCluster = dbAcces::get_LieByCluster($_SESSION["station_STA_NUM_CLUSTER"]);

	$MesLieu = NULL;

	foreach ($MesCluster as $LIE_NUM => $MonLieu)
	{
	    $MesLieu[] = $MonLieu["LIE_NOM"];
	}

	echo "<b>".implode(" / ", $MesLieu)."</b>";


	?>
    </td>
</tr>
<?php } ?>
<?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>$TitleTable,"colspanCenter"=>7,"colspanRight"=>4)); ?>
<tr class="EnteteTab">
    <td class="tdfixe" ></td>
    <td class="tdfixe colvide" ></td>
    <td class="tdfixe" colspan="3" align="center" style="font-size: 17px">Mensuel</td>
    <td class="tdfixe colvide"></td>
    <td class="tdfixe" colspan="3" align="center" style="font-size: 17px">Cumul</td>
    <td class="tdfixe colvide"></td>
    <td class="tdfixe" colspan="2" align="center" style="font-size: 17px">Ecarts sur cumuls</td>
</tr>

<tr class="EnteteTab">
    <td class="tdflotte" width="230"><div class="div180"></div></td>
    <td class="tdflotte colvide" width="1"></td>
<td width="60" class="tdflotte">R&eacute;alis&eacute;<div style="width: 70px;height:0px;"></div></td>
<td width="60" class="tdflotte">Pr&eacute;vu<div style="width: 70px;height:0px;"></div></td>
<td width="60" class="tdflotte">N-1<div style="width: 70px;height:0px;"></div></td>
<td class="tdflotte colvide" width="1"></td>
<td width="60" class="tdflotte">R&eacute;alis&eacute;<div style="width: 70px;height:0px;"></div></td>
<td width="60" class="tdflotte">Pr&eacute;vu<div style="width: 70px;height:0px;"></div></td>
<td width="60" class="tdflotte">N-1<div style="width: 70px;height:0px;"></div></td>
<td class="tdflotte colvide" width="1"></td>

<td width="60" class="tdflotte">R&eacute;al. - Pr&eacute;v.<div style="width: 70px;height:0px;"></div></td>
<td width="60" class="tdflotte">R&eacute;al. - (N-1)<div style="width: 70px;height:0px;"></div></td>

</tr> 

</thead>
<tbody>
<?php
//Définition des date au format jour-mois-année

$NbCols = 12;

$opt["colorsigne"] = array(10,11);

$LigneInsere = true;

foreach($LignesCarb as $codecompte => $UneLigne)
{
	if($LigneInsere)
	{
	    if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	    else $cssligne = 'bdligneimpaireTD';
	}

	
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

	echo $LigneInsere = table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




}

echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

$LigneInsere = true;

foreach($LignesProduits as $codecompte => $UneLigne)
{
	if($LigneInsere)
	{
	    if ($cssligne=='bdlignepaireTD') $cssligne = 'bdligneimpaireTD';
	    else $cssligne = 'bdlignepaireTD';
	}
	
	
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
        
        if($codecompte != "TOTALCARBURANTS") //ne pas afficher la ligne Total carburant
            echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




}

echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

$LigneInsere = true;

foreach($LignesCharges as $codecompte => $UneLigne)
{
	if($LigneInsere)
	{
	    if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	    else $cssligne = 'bdligneimpaireTD';
	}
	
	
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
	echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




}

echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

$LigneInsere = true;

foreach($LignesONFR as $codecompte => $UneLigne)
{
	if($LigneInsere)
	{
	    if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	    else $cssligne = 'bdligneimpaireTD';
	}
	
	
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
	echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




}



?>

<?php //include '../shell_prev/ONFR.php'; ?>

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