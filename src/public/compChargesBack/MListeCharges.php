<?php
if(!$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
<link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TG</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied

$MyHoldEntetePiedFalse = $EntetePiedFalse;
$EntetePiedFalse=true;

?>

<?php if($display == "produits" || !$display){ ?>

    <div align="center" style="width: 100%;">

    <?php

    $MesLignes = $LignesProduits;
    $Type = 'Produits';
    $Section = "compProd";
    include '../compChargesBack/MListe.php';
    ?>

    </div>

<?php } ?>

<?php  if($display == "charges" || !$display){ ?>
    
    <div align="center" style="width: 100%;">
    <?php
    $MesLignes = $LignesCharges;
    $Type = 'Charges';
    $Section = "compCharges";
    include '../compChargesBack/MListe.php';
    ?>

    </div>
    
<?php } ?>

<?php  if($display == "ONFR" || !$display){ ?>

    <div align="center" style="width: 100%;">
    <?php
    $MesLignes = $LignesONFR;
    $Type = 'ONFR';
    $Section = "compONFR";
    include '../compChargesBack/MListe.php';
    ?>

    </div>

<?php } ?>


<?php

$EntetePiedFalse = $MyHoldEntetePiedFalse;

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
 ?><?php

	/*$content = ob_get_clean();

	require_once(dirname(__FILE__).'/../html2pdf3.17/html2pdf.class.php');

	$html2pdf = new HTML2PDF('P','A4','fr');

	$html2pdf->WriteHTML($content, isset($_GET['vuehtml']));

	$html2pdf->Output('exemple00.pdf');*/
