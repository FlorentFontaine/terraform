<?php

session_start();

require_once('../dbClasses/AccesDonnees.php');

class compteposte_detail
{

    static function getTab($params)
    {
	$params["SCP_NUM"];


	$MesComptePostes = dbAcces::getPosteSimple($params);

        echo "Transfert du poste SHELL sur le(s) poste(s) MyReport suivant(s) :<br/><br/>";

	echo "<table class='tabBalance'
                bordercolordark='#000000'
                bordercolorlight='#000000'
                align='center'>";

	$FamilleDef = "";

	foreach ($MesComptePostes as $codePoste => $value)
	{

	    if($value["SsFamille"] != $FamilleDef)
		echo "<tr class='EnteteTab'><td><div class='div290'></div>".$value["SsFamille"]."</td></tr>";

	    $FamilleDef = $value["SsFamille"];

	    if($class=="bdligneimpaireTD") $class= "bdlignepaireTD"; else $class = "bdligneimpaireTD";

	    echo "<tr class='$class'><td align='left'>".$value["Libelle"]."</td></tr>";

	}

	echo "</table>";


    }
}

header('Content-type: text/html; charset=iso-8859-1');


compteposte_detail::getTab(array("SCP_NUM"=>$SCP_NUM));





?>
