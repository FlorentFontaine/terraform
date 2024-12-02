<?php

use Helpers\StringHelper;
use htmlClasses\TableV2;

if(!$EntetePiedFalse){?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Balance</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">

<style type="text/css">

.tabBalance .EnteteTab td
{
	text-align:center;
}

</style>

</head>
<?php

include("../include/entete.inc.php");

}//entetepied
?>

<center>

<?php if($AnoSensErrone) { 
$TitleTable = "Compte(s) avec un sens (D&eacute;bit/Cr&eacute;dit) erron&eacute;(s)";

}elseif($_GET["import"] && ! $_POST["Annulerimport"] && !$_SESSION["User"]->getAut($Section) ){ ?>
<!--<div class="titresection">IMPORTER UNE BALANCE</div>-->
<?php }else{ 
$TitleTable = "BALANCE DES COMPTES  - ".StringHelper::DateComplete(str_replace("-00","-01",$_SESSION["MoisHisto"]))." - ".$_SESSION["station_BALI_TYPE_exp"];
//echo $TitleTable;
 } ?>





<?php

if($_GET["import"] && ! $_POST["Annulerimport"] && !$_SESSION["User"]->getAut($Section) )
{
	
$notwindowonbeforeunload = true;
	
?>

<table style="width:600px">
    <?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>"IMPORTER UNE BALANCE","colspanLeft"=>"1","colspanCenter"=>"1","colspanRight"=>"1")); ?>
</table>
<br/>
<form method="post" enctype="multipart/form-data" >
    <table>
<tr>
    <td align="left">P&eacute;riode (mm/aaaa) : </td>
  <?php

 
 
	
  if(!$MaDernBal = station::GetLastBal($_SESSION["station_DOS_NUM"]))
  {
  	$MaDernBal = $_SESSION["station_DOS_DEBEX"];
  }

  	
  if($_SESSION["station_PREM_BAL"] || $correction || date("Y-m-00",strtotime(str_replace("-00","-01",$_SESSION["station_DOS_FINEX"]))) == $MaDernBal)//dans open.php
  	$NbMoisPlus = 0;
  else
  	$NbMoisPlus = 1;
  	
  	$block = "";
  	
  if($_SESSION["MoisHisto"] < $_SESSION["ioreport_maxMoisHisto"])
  {
  	$BAL_MOISNouv = date("m/Y",strtotime(str_replace("-00","-01",$_SESSION["MoisHisto"])));
  	
  	$block = " readonly='readonly' ";
  }
  	
  ?>
  <td align="left"><input type="text" <?php echo $block; ?> size="10" name="BAL_MOISNouv" value="<?php  if(!$BAL_MOISNouv) echo StringHelper::DatePlus(str_replace("-00","-01",$MaDernBal),array("moisplus"=>$NbMoisPlus,"dateformat"=>"m/Y")); else echo $BAL_MOISNouv; ?>"/></td>
 
</tr>

        <tr><td>&nbsp;</td><td></td></tr>
<?php 


if(StringHelper::DatePlus(str_replace("-00","-01",$MaDernBal),array("moisplus"=>$NbMoisPlus,"dateformat"=>"Y-m")) == date("Y-m",strtotime(str_replace("-00","-01",$_SESSION["station_DOS_FINEX"])))){ ?>
<tr>
<td align="left">Choisissez le type de balance : </td>
<td align="left">
    <?php 
    
    $MonImp = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"],$_SESSION["MoisHisto"]);
    
    ListeBalance::echo_Select_TypeBalance($MonImp[0]["BALI_TYPE"]);

    ?>
</td>
</tr>
        <tr><td>&nbsp;</td><td></td></tr>
<?php } ?>
<tr><td align="left">Choisissez le fichier d'import <b style='color:red;'>(format texte)</b> : </td><td><input type="file" name="fimport"  /></td></tr>


        <tr><td>&nbsp;</td><td></td></tr>

<tr>
    <td align="center">
	
    </td>
    <td>

	<input type="submit" class="button-spring" name="Annulerimport" value="Annuler"  style="float: left"/>
	<input type="submit" class="button-spring" name="validf" value="Importer &rarr;" style="float: right"/>

    </td>

</tr>
</table>
</form>
<br/><br/><br/>
<?php

}else{

?>

</center>
<form method="post" action="Liste.php?correctionBal=1">


<input type="hidden" name="Enregistrement"  value="1"/>
<center>
<?php

$MonImp = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"],$_SESSION["MoisHisto"]);	

if($correctionBal && !$_SESSION["User"]->getAut($Section) && $_SESSION['ModifOK'])
{
?>
<div style="position: fixed;top: 50%;left: 1%;border: 1px solid red;padding: 5px;background: white;">
<input type="submit" class="button-spring" name="validCorrectBal" value="Enregistrer" style="width: 120px;"/>
    <br/><br/>
    <input type="submit" class="button-spring" name="AnnulCorrectBal" style="width: 120px;" value="Annuler" />
    <br/><br/>
<?php if( false && date("Y-m",strtotime(str_replace("-00","-01",$_SESSION["MoisHisto"]))) == date("Y-m",strtotime(str_replace("-00","-01",$_SESSION["station_DOS_FINEX"])))){


?>
<table>
<tr>
<td align="left">Type de balance : </td>
<td align="left">

    <?php ListeBalance::echo_Select_TypeBalance($MonImp[0]["BALI_TYPE"]); ?>

</td>

</tr></table>
<br/>
<?php }else{ ?><input type="hidden" value="<?php echo $MonImp[0]["BALI_TYPE"]; ?>" /> <?php } ?>

D&eacute;s&eacute;quilibre : <?php echo StringHelper::NombreFr(- $Equilibrage,2,true,true); ?> &euro; <br/><br/><a href="#" onclick="document.getElementById('changeClasse').click();">[ Recalculer ]</a>
</div>
	<?php
}
?>

<input type="hidden" name="correctionBal" id="correctionBal" value=""/>
<input type="hidden" name="classe" id="classe" value="<?php echo $classe; ?>"/>
<input type="submit" name="changeClasse" id="changeClasse" value="changement classe" style="display:none" />
</center><br/>

<script type="text/javascript">
ent_TableIds.push("tab_Balance");
</script>


<table  dir="IMP_PDF;ORIENTATION:PORTRAIT;TITLETABLE:BALANCE;EXTANDTABLE:1;BORDER:1;FITHEIGHT:50;FREEZEPLAN:B5;" style="width:800px" class="tabBalance" align="center" bordercolordark=#000000 bordercolorlight=#000000 id="tab_Balance">
<thead >
    <?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>$TitleTable,"colspanLeft"=>"2","colspanCenter"=>"3","colspanRight"=>"3")); ?>
<tr class="EnteteTab">
<td></td><td></td>
<td colspan="3">Balances exercice N</td>
<td class="col_sep"></td>
<td colspan="2">Balances exercice N-1</td>
</tr>

<tr class="EnteteTab sticky">

<td  width="50" class="tdfixe"><div class="div90"></div>Comptes</td>
<td width="150" class="tdfixe"><div class="div140"></div>Libell&eacute;s</td>
<td  class="tdfixe" width="50"><div class="div90"></div>Cumul<br/>  M-1</td>
<td  class="tdfixe" width="50"><div class="div90"></div>Cumul<br/>  M</td>
<td  class="tdfixe" width="50"><div class="div90"></div>Mois</td>
<td class='col_sep' width="2"></td>
<td  class="tdfixe" width="50"><div class="div90"></div>Cumul</td>
<td  width="50"  class="tdfixe"><div class="div90"></div>Mois</td>
</tr>
</thead>
<tbody>
<?php


foreach($MesLignes as $codecompte => $UneLigne)
{
	if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	else $cssligne = 'bdligneimpaireTD';
	echo table::getLine($UneLigne,array("class"=>$cssligne),8);
}

?>
</tbody>

</table>
</form>
<?php

}

if(!$EntetePiedFalse)
{
?>

<?php
include("../include/pied.inc.php");


if($ErreurImport && !$MessErr)
{
	$MessErr = $ErreurImport;
}

if($MessErr)
{
	if(stristr($MessErr,"##EQUILIBRE##||"))
	{
		$MaDiff = str_replace("##EQUILIBRE##||","",$MessErr);
		
		$MaDiff = -$MaDiff;
		
		$MaDiff = StringHelper::NombreFr($MaDiff,2,true,true);
		
		$MessErr = "Les &eacute;critures ne sont pas &eacute;quilibr&eacute;es ! <br/><br/>D&eacute;s&eacute;quilibre : ".$MaDiff;
	}
	
	echo "<div id='monID_DBOX'>".$MessErr."</div>";

	
	?>
	<script type="text/javascript" language="javascript">
	
	
        $("#monID_DBOX").dialog({
            title: "ERREUR",
            width: "300",
            height: "200",
            modal: true,
            resizable: false,
            
            buttons:{
                "OK": function(){
                    $(this).dialog("close");
                }
            }
        });
	
	
	</script>
	<?php
}

?>


</body>
</html>
<?php

}//entetepied

?>