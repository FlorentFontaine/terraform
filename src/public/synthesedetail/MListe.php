<?php use Helpers\StringHelper;
      use htmlClasses\TableV2;

if(!$EntetePiedFalse)
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>
<?php

if($Produits=='1')
	echo "D&eacute;tail des comptes produits";
if($Type == "Charges")
	echo "D&eacute;tail des comptes charges";

?>
</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">

</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied
?>

<center>
<!--<div class="titresection">-->
<?php
if($Type=="Produits")	$TitleTable = "DETAIL DES COMPTES PRODUITS";
else	$TitleTable = "DETAIL DES COMPTES CHARGES";
$TitleTable .= " - ".StringHelper::DateComplete(str_replace("-00","-01",$_SESSION["MoisHisto"]))." - ".$_SESSION["station_BALI_TYPE_exp"];

//echo $TitleTable;
?>
<!--</div>-->
</center><br/><br/>

<center>
<table width="80%"><tr><td>

<script type="text/javascript">
ent_TableIds.push("tab_DetailChargeProd");
</script>

<table dir="IMP_PDF;FITHEIGHT:false;TITLETABLE:<?php echo $TitleTable; ?>;FREEZEPLAN:I2;"  align="center" class="tabForm"  bordercolordark="#000000" bordercolorlight="#000000" id="tab_DetailChargeProd">
<?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>$TitleTable,"colspanLeft"=>"2","colspanCenter"=>"1","colspanRight"=>"5")); ?>
<tbody>
<?php

$NbCols = 8;
foreach($MesLignes as $codecompte => $UneLigne){
	//if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	//else $cssligne = 'bdligneimpaireTD';	

        $cssligne1 = "";

	if(stristr($codecompte,"STOTAL") || stristr($codecompte,"Poste"))
            $cssligne1 = "bolder";
	elseif(stristr($codecompte,"ENCADRE"))
            $cssligne1 = "EnteteTab";
	elseif(!stristr($codecompte,"Total") && !stristr($codecompte,"VIDE") )
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
<!--

<?php
if($Type != "Produits"){    ?>
	<br />
	<table border="1" align="left">
	<tr class="EnteteTab">
	<td>Masse salariale</td><td>Pr&eacute;visionnel</td>
	</tr>
	<tr class="bdligneimpaireTD"><td>Nb d'heures salari&eacute;s pay&eacute;es : </td><td>&nbsp;</td></tr>
	<tr class="bdlignepaireTD"><td>Status de la g&eacute;rance : </td><td>&nbsp;</td></tr>
	<tr class="bdligneimpaireTD"><td>Nb d'heures pay&eacute;es :</td><td>&nbsp;</td></tr>
	<tr class="EnteteTab" style="text-align:right">
	  <td align="right" >Tau horaires personnel : </td>
	  <td align="right">0,00</td></tr>
	<tr class="EnteteTab" style="text-align:right">
	  <td align="right">Taux horaire station </td>
	  <td align="right">0,00</td></tr>
	</table>
	
<?php
}   ?>
-->
</td></tr></table>
</center>


<?php   if(!$EntetePiedFalse){    include("../include/pied.inc.php");   ?>
</body>
</html>
<?php
}   ?>