<?php

session_start();

require_once('../dbClasses/AccesDonnees.php');

class quotepart_detail
{
    
    static function getTab($params)
    {
	$params["QUOTEPART_NUM"];

	
	$MesComptePostes = dbAcces::getCP_NUM_par_QUOTE_PART($params["QUOTEPART_NUM"]);

	echo "<table>";


	$FamilleDef = "";

	foreach ($MesComptePostes[$params["QUOTEPART_NUM"]] as $codePoste => $value)
	{

	    if($value["SsFamille"] != $FamilleDef)
		echo "<tr class='EnteteTab'><td><div class='div200'></div>".$value["SsFamille"]."</td></tr>";

	    $FamilleDef = $value["SsFamille"];

	    if($class=="bdligneimpaireTD") $class= "bdlignepaireTD"; else $class = "bdligneimpaireTD";

	    echo "<tr class='$class'><td align='left'>".$value["Libelle"]."</td></tr>";

	}

	echo "</table>";


    }
}

header('Content-type: text/html; charset=iso-8859-1');


quotepart_detail::getTab(array("QUOTEPART_NUM"=>$QUOTEPART_NUM));





?>
