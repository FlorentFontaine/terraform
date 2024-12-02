<?php
use Helpers\StringHelper;
session_start();
include_once 'session.php';
include_once '../ctrl/ctrl.php';
include_once '../dbClasses/station.php';

if($STA_NUM && $_SESSION["station_STA_NUM"] && $STA_NUM != $_SESSION["station_STA_NUM"])
	unset($_SESSION["station_STA_NUM"]);

if(!$STA_NUM)
{
	if($_SESSION["station_STA_NUM"])
		$STA_NUM = $_SESSION["station_STA_NUM"];
	else
		exit();
}


	
if($valid)
{
	if($DOS_NUM = station::NouvelleExercice($_POST,$erreur,$Warning,$_POST["Confirm"]))
	{
		
		include("../StationBack/open.php");
	}
	
	$_POST["DOS_DEBEX"] = StringHelper::DateFr2MySql($_POST["DOS_DEBEX"]);
	$_POST["DOS_FINEX"] = StringHelper::DateFr2MySql($_POST["DOS_FINEX"]);
	
	$_POST["DOS_DEBEXN1"] = StringHelper::DateFr2MySql($_POST["DOS_DEBEXN1"]);
	$_POST["DOS_FINEXN1"] = StringHelper::DateFr2MySql($_POST["DOS_FINEXN1"]);
	
	//$_POST["DOS_DEBEXCP"] = date("Y-m-00",strtotime($_POST["DOS_DEBEXCP"]));
	$_POST["DOS_DEBEXCP"] = StringHelper::DateFr2MySql($_POST["DOS_DEBEXCP"]);
	$_POST["DOS_DEBEXCP"] = date("Y-m-00",strtotime($_POST["DOS_DEBEXCP"]));
	
	/*
	if(!StringHelper::isDateValide($_POST["DOS_DEBEX"]))
		$_POST["DOS_DEBEX"] = "0000-00-00";
		
	if(!StringHelper::isDateValide($_POST["DOS_FINEX"]))
		$_POST["DOS_FINEX"] = "0000-00-00";
		*/
	if(!StringHelper::isDateValide($_POST["DOS_DEBEXCP"],true))
		$_POST["DOS_DEBEXCP"] = "0000-00-00";
	
	if(!StringHelper::isDateValide($_POST["DOS_DEBEXN1"]))
		$_POST["DOS_DEBEXN1"] = "0000-00-00";
	
	if(!StringHelper::isDateValide($_POST["DOS_FINEXN1"]))
		$_POST["DOS_FINEXN1"] = "0000-00-00";
		
	$lnDossier = $_POST;
	
	
	
}
elseif($UpdateDossier)
{
	$lnDossier = station::GetExercice($STA_NUM,$UpdateDossier);
	
	
	$lnDossierN1 = station::GetLastEx($STA_NUM,$lnDossier["DOS_NUMPREC"]);
	
	foreach($lnDossierN1 as $cle => $Val)
	{
		$lnDossier[$cle] = $Val;
	}
	
	$lnDossier["DOS_DEBEXCP"] = $lnDossier["DOS_PREMDATECP"];
	
}
else
{
	$lnDossier = station::GetLastEx($STA_NUM);
	//var_dump($lnDossier);
}

$MonLieuDossier = dbAcces::getStation($STA_NUM);
$MonLieuDossier = $MonLieuDossier[$STA_NUM]["LIE_NUM"];


$MaStation = station::GetStation($STA_NUM);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Soci&eacute;t&eacute;</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
</head>

<body >

<?php
include("../include/entete.inc.php");

?>

<center>
<!--<div class="titresection"><?php //if($UpdateDossier) { ?>Modification<?php //}else{ ?>Nouvel<?php //} ?> exercice &rarr; <?php //echo $MaStation["LIE_NOM"]." : ".$MaStation["STA_SARL"]?> </div>-->
<br/><br/>
<form method="post">
<input type="hidden" name="STA_NUM" value="<?php echo $STA_NUM; ?>"/>
<input type="hidden" name="LIE_NUM" value="<?php echo $MonLieuDossier; ?>"/>
<?php
if($lnDossier["DOS_DEBEXN1"] && $lnDossier["DOS_DEBEXN1"] != "0000-00-00")
{
	?>
	<input type="hidden" name="N1" value="1"/>
	<?php
}

?>

<input type="hidden" name="DOS_NUMPREC" value="<?php echo $lnDossier["DOS_NUMPREC"]; ?>"/>

<?php
if($UpdateDossier)
{
	?>
	<input type="hidden" name="UpdateDossier" value="<?php echo $UpdateDossier; ?>"/>
	<?php
}
?>

<table style="width: 640px;">
    <tr>
        <td class="EnteteTab TitreTable"  style="text-align:center;font-weight:bold;border:none">
            <?php if ($UpdateDossier)
            { ?>Modification<?php } else
            { ?>Nouvel<?php } ?> exercice &rarr; <?php echo $MaStation["LIE_NOM"] . " : " . $MaStation["STA_SARL"] ?>
        </td>
    </tr>  
</table>
  <br/>
<table class='bdFormulaireTableau' style="width: 640px;padding: 20px;" >
  <tr>
    <td class='bdFormulaireTitre'>Date et dur&eacute;e N-1 </td>
    <td>&nbsp;</td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td class='bdFormulaireTitre'>Date et dur&eacute;e N </td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td></td>
    <td>&nbsp;</td>
    <td></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td class='bdFormulaireTitre'>Date d&eacute;but exercice </td>
    <td class='bdFormulaireTitre'><input type="text" size="10"  name="DOS_DEBEXN1" value="<?php echo StringHelper::Mysql2DateFr($lnDossier["DOS_DEBEXN1"]); ?>"  readonly='readonly' /></td>
    <td class='bdFormulaireTitre'></td>
    <td class='bdFormulaireTitre'>Date d&eacute;but exercice </td>
    <td class='bdFormulaireTitre'><input type="text" size="10" name="DOS_DEBEX" id="DOS_DEBEX" value="<?php
    
    if($lnDossier["DOS_NBMOISN1"] == 23)
    	$MoisPlus = 24;
    else
    	$MoisPlus = $lnDossier["DOS_NBMOISN1"];
    	
    
    if(!$lnDossier["DOS_DEBEX"]) 
    	echo $DebNouv = StringHelper::DatePlus($lnDossier["DOS_FINEXN1"],array("joursplus"=>1,"dateformat"=>"d/m/Y"));
    else
    	echo StringHelper::Mysql2DateFr($lnDossier["DOS_DEBEX"]);

    ?>" tabindex="10"
    
    <?php
	
    //$sql = "select distinct DOS_NUM from balanceimport where DOS_NUM = '".$lnDossier["DOS_NUM"]."'";
    
    if($lnDossier["DOS_NUM"])
    {
    	echo " readonly='readonly' ";
    }
    
    ?>
    
    /></td>

  </tr>
  <tr>
    <td class='bdFormulaireTitre'>Date fin exercice </td>
    <td class='bdFormulaireTitre'><input type="text" size="10" name="DOS_FINEXN1" value="<?php echo StringHelper::Mysql2DateFr($lnDossier["DOS_FINEXN1"]); ?>" readonly='readonly' /></td>
    <td class='bdFormulaireTitre'></td>
    <td class='bdFormulaireTitre'>Date fin exercice </td>
    <td class='bdFormulaireTitre'><input type="text" size="10" name="DOS_FINEX" id="DOS_FINEX" value="<?php
    if(!$lnDossier["DOS_FINEX"]) 
    	echo StringHelper::DatePlus($lnDossier["DOS_FINEXN1"],array("moisplus"=>$MoisPlus,"dateformat"=>"d/m/Y")); 
    else
    	echo StringHelper::Mysql2DateFr($lnDossier["DOS_FINEX"]);
    	
    ?>" tabindex="20"/></td>
  </tr>
  <tr>
    <td class='bdFormulaireTitre'>Dur&eacute;e en mois </td>
    <td class='bdFormulaireTitre'><input type="text" size="10" name="DOS_NBMOISN1" value="<?php echo $lnDossier["DOS_NBMOISN1"]; ?>" readonly='readonly'/></td>
    <td class='bdFormulaireTitre'></td>
    <td class='bdFormulaireTitre'>Dur&eacute;e en mois </td>
    <td class='bdFormulaireTitre'><input type="text" size="10" name="DOS_NBMOIS" id="DOS_NBMOIS"  tabindex="30" value="<?php 
    
    if(!$lnDossier["DOS_NBMOIS"]) 
    	echo $lnDossier["DOS_NBMOISN1"]; 
    else
    	echo $lnDossier["DOS_NBMOIS"]; 
    
    ?>"/></td>
  </tr>
  <tr>
    <td><br/><br/></td>
    <td>&nbsp;</td>
    <td></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5" style="text-align: center;" class='bdFormulaireTitre'>Premi&egrave;re p&eacute;riode de l'exercice N-1 &agrave; comparer avec la premi&egrave;re p&eacute;riode de l'exercice N </td>
    </tr>
  <tr>
    <td></td>
    <td colspan="3" align="center">

    <?php
	//var_dump($lnDossier["DOS_DEBEXCP"]);
    ?>
    <input type="text" size="10" tabindex="40" name="DOS_DEBEXCP" id="DOS_DEBEXCP" value="<?php 
    
    if(!$lnDossier["DOS_DEBEXCP"] || $lnDossier["DOS_DEBEXCP"] == "0000-00-00") 
	{
		if($lnDossier["DOS_DEBEXN1"] && $lnDossier["DOS_DEBEXN1"] != "0000-00-00")
			echo StringHelper::Mysql2DateFr(date("Y-m-00",strtotime($lnDossier["DOS_DEBEXN1"]))); 
	}
    else 
    	echo StringHelper::Mysql2DateFr($lnDossier["DOS_DEBEXCP"]); 
    	
    ?>"
    <?php
    if(!$lnDossier["DOS_DEBEXN1"] || $lnDossier["DOS_DEBEXN1"] == "0000-00-00")
    	echo " readonly='readonly' ";
     ?>   
    /></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
    
  </tr>
  
  <?php

  if($Warning)
  {
  	?>
  	
  	<tr>
  	<td style="color:red" colspan="5" align="center">
  	ATTENTION : <br/><br/>
  	<?php

  	foreach($Warning as $cle => $MessageWarning)
  	{
  		
  		echo "- $MessageWarning <br/>";
  	}
  	
  	?>
  	
  	<script type="text/javascript" >
	
	document.getElementById('<?php echo $cle; ?>').focus();
	document.getElementById('<?php echo $cle; ?>').select();
	
	</script>
  	
  	<br/>
  	<label style="color: black"><input type="checkbox" name="Confirm" value="1"/> Je confirme ma saisie</label>
  	</td>
  	</tr>
  	
  	
  	<?php
  }
  
  
  ?>
  
  
  <tr><td colspan="5" ><br/>
  <table style="width:100%"><tr>
  	<td align="left">
	
	<?php

	if(!$MyReferrer && !strpos($_SERVER["HTTP_REFERER"], "formulaire2.php") === false && !$_SESSION["station_STA_NUM"])
	{
		$MyReferrer = "../StationBack/Liste.php";
	}
	else
	{
		$MyReferrer = "../GardeBack/Garde.php";
	}
	?>
	
	<input type="hidden" name="MyReferrer" value="<?php echo $MyReferrer; ?>"/>
	
	<input type="button" onclick="document.location.href='<?php echo $MyReferrer; ?>'" value="Annuler"/> 
	
	</td>
  	<td align="right"><b style="color:red">Pensez &agrave; cr&eacute;er l'exercice N-1 en premier !</b><br/><br/><input type="submit" name="valid" tabindex="50" value="<?php if($UpdateDossier){ ?>Enregistrer<?php }else{ ?>Continuer<?php } ?>" /> </td>
  </tr></table>
  </td></tr>
  
</table>
</form>
<?php

if($erreur)
{
	$ErreurStr = "";
	$cleFocus = false;
	
	foreach($erreur as $cle=>$val)
	{
		if(!$cleFocus)
			$cleFocus =	$cle;
		
		$ErreurStr .= " - ".$val."<br/>";
	}
	
	
	?>
	<script type="text/javascript" >
	
	customAlert("Erreur","<?php echo $ErreurStr; ?>",function(){
        document.getElementById('<?php echo $cleFocus; ?>').focus();
        document.getElementById('<?php echo $cleFocus; ?>').select();
	});
	
	
	
	
	
	</script>
	<?php
}

?>

<?php if(!$Warning && !$erreur) { ?>

<?php } ?>
</center>
<?php
include("../include/pied.inc.php");
?>
</body>
</html>
