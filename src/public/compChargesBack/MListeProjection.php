<?php
if(!$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Projection</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied
$Projection = true;
$EntetePiedFalse = true;
$MesLignes = $LignesProduits;
$Type = 'Produits';
$Section = "compProd";

include '../compChargesBack/MListe.php';
?>
<div class="breakafter">&nbsp;</div>
<?php

if($Imprimer)
	Impression::Entete();
	


$MesLignes = $LignesCharges;
$Type = 'Charges';
$Section = "compCharges";
include '../compChargesBack/MListe.php';
$EntetePiedFalse = false;
?>


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
