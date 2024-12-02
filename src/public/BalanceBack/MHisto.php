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
<body >

<?php

use htmlClasses\TableV2;

include("../include/entete.inc.php");
?>


<center>
<div class="titresection">
BALANCE DES COMPTES
</div>



</center>

<script type="text/javascript">
ent_TableIds.push("tab_Balance_Histo");
</script>

    <table style="border:1px solid #000000" class="tabBalance" id="tab_Balance_Histo" align="center" bordercolordark=#000000 bordercolorlight=#000000 >
<thead >
<?php



echo table::getLine($MaLigneEntete,array("class"=>"EnteteTab"),count($UneLigne));

?>
</tr>
</thead>
<tbody>
<?php


foreach($MesLignes as $codecompte => $UneLigne)
{
	if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
	else $cssligne = 'bdligneimpaireTD';
	echo table::getLine($UneLigne,array("class"=>$cssligne),0);
}

?>
</tbody>
</table>


<?php
include("../include/pied.inc.php");
?>
</body>
</html>
