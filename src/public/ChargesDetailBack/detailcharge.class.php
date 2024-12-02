<?php
use Helpers\StringHelper;
session_start();
include_once '../ctrl/ctrl.php';
require_once('../dbClasses/AccesDonnees.php');
require_once('../htmlClasses/table.php');






class DetailChargeProd
{
    static function getTab($Type,$MoisActuel,$FamilleSelect = false,$SsFamilleSelect = false,$codePosteSelect = false,$resume = false,$Periode=false)
    {

	global $Imprimer;


	if(!$Periode["BAL_MOIS_DEB"] || !$Periode["BAL_MOIS_FIN"])
            $Periode = NULL;

	if($Type == "Produits")
        {
                //$TypeCompte = array("test"=>"=","type"=>"vente");//pour la récupération des résultat avec tout les compte de la balance sauf les compte d'achat
	    $RRRO = array("test"=>"=","RRRO"=>"0");
            }


	if($Type)
                $Where["and"]["compteposte.type"] = "='$Type' ";

                
	if($codePosteSelect)
            {
                $plus = &$Where["and"];
                $plus["comptePoste.codePoste"] = "='$codePosteSelect'";
            }

	if($SsFamilleSelect)
            {
                $plus = &$Where["and"];
                $plus["comptePoste.SsFamille"] = "=\"$SsFamilleSelect\"";
            }


	if($FamilleSelect)
            {
                $plus = &$Where["and"];
	    $plus["comptePoste.Famille"] =  "=\"$FamilleSelect\"";
            }

            $tri = array("ordre" => "ASC");

	$MesPostes = dbAcces::getPosteVisible($Where,$tri);


        $MaCleTab = NULL;
	$TotauxF = NULL;
	$TotauxSF = NULL;
	$PremF = true;
	$PremSF = true;
	$FaireSToataux = true;

	if($MoisActuel)
            {
	    $MesResultatsCompte = dbAcces::getResultatsCompte($MoisActuel,false,false,false);
	    $MesResultatsCompteNMoins1 = dbAcces::getResultatsCompte($MoisActuel,false,true);
	    $MesResultatsCompteNMoins1Cumul = dbAcces::getResultatsCompte($MoisActuel,false,true,false,false,false,false,false,false,false,true);
	}

	//initialisation du tableau avec ligne total + stotal de chaque postes
	foreach($MesPostes as $codePoste => $UneLignePoste)
	{
	    if($codePosteSelect)
	    {
		$Type = $UneLignePoste["Type"];
	    }

	    $UneLigneTableau = NULL;
	    if($Type == "Produits")
	    {
		if($UneLignePoste["Famille"] != "ONFR" || true)
		{
		    //,"codePoste_ONFR"=>" <= 0"
		    if($codePoste == 77)
			$Where1 = array("and" => array("codePoste" => "='$codePoste'","RRRO"=>"=0  or comptes.numero = '6040000'","type"=>"!='achat'"));
		    elseif($codePoste == 28)
			$Where1 = array("and" => array("codePoste" => "='$codePoste'","RRRO"=>"=0","type"=>"!='achat'"));
		    else
			$Where1 = array("and" => array("codePoste" => "='$codePoste' ","RRRO"=>"=0","type"=>"!='achat'"));


		    //if($shell) $Where1["and"]["codePoste_ONFR"] = "='0' ";


		}
		else
		{
		    $Where1 = array("and" => array("codePoste_ONFR" => "='$codePoste' ","RRRO"=>"=0","type"=>"!='achat'"));
		}

	    }
	    elseif($Type == "Charges")
	    {

		/*$Where1 = array("and" => array("codePoste" => "='$codePoste' "));*/

		if($UneLignePoste["Famille"] != "ONFR" || true)
		{
		    $Where1 = array("and" => array("codePoste" => "='$codePoste' "));

		    /*if($shell)
			$Where1["and"]["codePoste_ONFR"] = "='0' ";*/

		}
		else
		    $Where1 = array("and" => array("codePoste_ONFR" => "='$codePoste' "));



	    }

		$MesComptes = dbAcces::getComptes($Where1);

	    $MaSsFamille = explode("||#||",$UneLignePoste["SsFamille"]);
	    $MaSsFamille = $MaSsFamille[0];

	    if($UneLignePoste["SsFamille"] != $SsFamilleDef )
	    {  //changement de Sousfamille
		if($SsFamilleDef != $FamilleDef && !$resume && $MoisActuel)
		{
                    //Ln vide
                    $UneLigneTableau = NULL;
		    $UneLigneTableau[] = array(""=>array("colspan"=>"8"));//Orel 03022010 colspan 6
		    $MesLignesTableau["VIDE".$codePoste] = $UneLigneTableau;
		    $UneLigneTableau = NULL;
                    
                    //LnsTotal
		    $Nom = explode("||#||",$SsFamilleDef);

		    $Nom = "Total ".$Nom[0]." :";

		    $UneLigneTableau[] = array($Nom=>array('align'=>'right','style'=>'border:none;'));
		    $MesLignesTableau["STOTAL".$SsFamilleDef] = $UneLigneTableau;
		    $UneLigneTableau = NULL;
		    //Ln vide
		    $UneLigneTableau[] = array("&nbsp;"=>array("colspan"=>"8"));//Orel 03022010 colspan 6
		    $MesLignesTableau["ESPACE".$codePoste] = $UneLigneTableau;
		    $UneLigneTableau = NULL;

		}

		if($UneLignePoste["Famille"] == $FamilleDef)//ln nom sous-famille
		{

		    $UneLigneTableau[] =  array($MaSsFamille=>array("style"=>"padding: 5px;text-align:left; ","class"=>"tdfixe","width"=>"280"));

		    if($MoisActuel)
		    {
			if(!$resume)
			    $UneLigneTableau[] = array("P&eacute;riode N"=>array("class"=>"tdfixe","width"=>"40","style"=>"border:none"));
			else
			    $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel)=>array("class"=>"tdfixe","width"=>"40","style"=>"border:none"));
		    }


		    if(!$resume)
		    {
			$UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"2"));//Orel 03022010

			$UneLigneTableau[] = array("P&eacute;riode N-1"=>array("class"=>"tdfixe","width"=>"40"));
			$UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"20"));
			$UneLigneTableau[] = array("Cumul N"=>array("class"=>"tdfixe","width"=>"40"));

			$UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"2"));//Orel 03022010

			$UneLigneTableau[] = array("Cumul N-1"=>array("class"=>"tdfixe","width"=>"40"));
		    }
		    $MesLignesTableau["ENCADRE".$UneLignePoste["SsFamille"]] = $UneLigneTableau;
		    $UneLigneTableau = NULL;
		}
		$SsFamilleDef =NULL;
		$SsFamilleDef = $UneLignePoste["SsFamille"];

	    }

	    if($UneLignePoste["Famille"] != $FamilleDef)//changement de famille
	    {
		//ln nom famille

		if($FamilleDef)
		{//séparation
		    $UneLigneTableau = NULL;

		    $UneLigneTableau[] = "<div style='height:100px;'></div>";

		    $MesLignesTableau["SEPAR".$UneLignePoste["Famille"]] = $UneLigneTableau;
		}
		$UneLigneTableau = NULL;

		$UneLignePoste["Famille"] = str_replace("ONFR", "ACTIVITES ANNEXES", $UneLignePoste["Famille"]);

		//$UneLigneTableau[] = array("<div class='titresectionreverse' style='width:100%;'>".$UneLignePoste["Famille"]."</div>"=>array("colspan"=>"8","style"=>"padding:0px"));


		if(!$resume)
		    $MesLignesTableau["FAMILLE".$UneLignePoste["Famille"]] = $UneLigneTableau;



		$UneLigneTableau = NULL;
		$UneLigneTableau[] =  array($MaSsFamille."<div class='div200' style='width:350px'></div>"=>array("style"=>"padding: 5px;text-align:left; ","class"=>"tdfixe","width"=>"280"));


		if($MoisActuel)
		{

		    if(!$resume)
			$UneLigneTableau[] = array("P&eacute;riode N<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));
		    else
			$UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel)."<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));

		}
		elseif($Periode)
		{
		    if($resume)
			$UneLigneTableau[] = array(StringHelper::Mysql2DateFr($Periode["BAL_MOIS_DEB"])." &rarr; ".StringHelper::Mysql2DateFr($Periode["BAL_MOIS_FIN"])."<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"70"));
		}

		if(!$resume)
		{
		    $UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"1"));//Orel 03022010
		    $UneLigneTableau[] = array("P&eacute;riode N-1<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));
		    $UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"20"));
		    $UneLigneTableau[] = array("Cumul N<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));
		    $UneLigneTableau[] = array(""=>array("class"=>"tdfixe","width"=>"1"));//Orel 03022010
		    $UneLigneTableau[] = array("Cumul N-1<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));
		}
		$MesLignesTableau["ENCADRE".$UneLignePoste["SsFamille"]] = $UneLigneTableau;
		$UneLigneTableau = NULL;

		$FamilleDef = $UneLignePoste["Famille"];
		$PremSF = true;
		$FaireSToataux = false;
	    }

	    $UneLigneTableauTitre = NULL;

	    $UneLigneTableauTitre[] = array(" - $toto".strtoupper(StringHelper::TextFilter(StringHelper::accentToNoAccent($UneLignePoste["Libelle"])))=>array("align"=>"left", "height" => "25", "style" => "height: 15px;"));

	    $UneLigneTableau = NULL;

	    $AuMoinUnCompte = false;

	    foreach($MesComptes as $codeCompte => $UneLigneCompte)
	    {

		if((!$Imprimer) || ( $MesResultatsCompte[$codeCompte."UMois"]["BAL_BALANCE"] || $MesResultatsCompte[$codeCompte."UMois"]["BAL_CUMUL"] || $MesResultatsCompteNMoins1[$codeCompte."UMois"]["BAL_BALANCE"] || $MesResultatsCompteNMoins1Cumul[$codeCompte."UMois"]["BAL_CUMUL"]))
		{
		    if(!$AuMoinUnCompte)
			$MesLignesTableau["Poste".$codePoste] = $UneLigneTableauTitre;
		    $AuMoinUnCompte = true;
		    $UneLigneTableau = NULL;



		    $UneLigneTableau[] =  array($UneLigneCompte["numero"]." &nbsp; ".$UneLigneCompte["libelle"]=>array("align"=>"left"));

		    $ComplementNom = "";

		    if($UneLignePoste["Famille"] == "ONFR")
			$ComplementNom = "||#||ONFR";

		    $MesLignesTableau[$codeCompte.$ComplementNom] = $UneLigneTableau;
		}
	    }

	    if($AuMoinUnCompte && $MoisActuel)
	    {


		$UneLigneTableau = NULL;
		//$UneLigneTableau[] = array("Total -"=>array("align"=>"right","style"=>"font-weight: bolder")); Orel 03022010
		$UneLigneTableau[] = array(""=>array("align"=>"right","style"=>"font-weight: bolder"));
		$MesLignesTableau["Total".$codePoste] = $UneLigneTableau;
		$PosteDef = $UneLignePoste["Libelle"];
		$UneLigneTableau = NULL;
		//Ln vide
		$UneLigneTableau[] = array(""=>array("colspan"=>"8"));//Orel 03022010 (colspan 6
		$MesLignesTableau["VIDE2".$codePoste] = $UneLigneTableau;
		$UneLigneTableau = NULL;
	    }


	    if($PremSF) $PremSF=false;
	    if($PremF)  $PremF=false;
	}




	if($Type == "Charges" && $AuMoinUnCompte && !$resume && $MoisActuel)
	{

	    $UneLigneTableau = NULL;
	    $UneLigneTableau[] = array(""=>array("align"=>"right","style"=>"font-weight: bolder"));
	    $MesLignesTableau["Total".$codePoste] = $UneLigneTableau;
	    $PosteDef = $UneLignePoste["Libelle"];
	    $FaireSToataux=true;
	}

	if($Type == "Produits" && $AuMoinUnCompte && !$resume && $MoisActuel)
	{

	    $UneLigneTableau = NULL;
	    $UneLigneTableau[] = array(""=>array("align"=>"right","style"=>"font-weight: bolder"));
	    $MesLignesTableau["Total".$codePoste] = $UneLigneTableau;
	    $PosteDef = $UneLignePoste["Libelle"];
	    $FaireSToataux=true;
	}



	//***************************************/

	//LnsTotal
	if($FaireSToataux && !$resume && $MoisActuel)
	{
	    $Nom = explode("||#||",$SsFamilleDef);

	    if(count($Nom) > 1)	$Nom = "Sous total :";
	    else			$Nom = "Total ".$SsFamilleDef." :";

	    $UneLigneTableau = NULL;
	    $UneLigneTableau[] = array($Nom=>array('align'=>'right','style'=>'font-weight: bolder;'));

	    $MesLignesTableau["STOTAL".$SsFamilleDef] = $UneLigneTableau;

	}


	$UneLigneTableau = NULL;
	//LnTotal
	//$UneLigneTableau[] = array("TOTAL ".$FamilleDef." :"=>array('style'=>'font-weight: bolder'));
	//$MesLignesTableau["TOTAL".$FamilleDef] = $UneLigneTableau;

	//mise en place des résultats comptes Période N

	if($MoisActuel)
	{
	    //var_dump($MesResultatsCompte);
	    foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codeCompte, 'ENCADRE') !== false) {
			$Famille = str_replace("ENCADRE", "", $codeCompte);
		} elseif (strpos($codeCompte, 'Poste') !== false) {
			$codePoste = str_replace("Poste", "", $codeCompte);
		}

		
		elseif(is_numeric($codeCompte))
		{
		    //c'est une ligne d'un compte
		    //echo StringHelper::NombreFr($MesResultatsCompte[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";
		    $reacompte = $MesResultatsCompte[$codeCompte."UMois"]["BAL_BALANCE"];

		    if($Type ==  "Produits")
		    {
			$reacompte = -$reacompte;
		    }

		    $MesResultats[$codePoste."||#||"."UMoisRealise"]["Montant"] += $reacompte;
		    $MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"] = $Famille;
		    $UneLigneDb[] =  array(StringHelper::NombreFr($reacompte)=>array("align"=>"right"));
		    if(!$resume)
			$UneLigneDb[] = "";//colone vide
		}
	    }
	}

	if(!$resume)
	{
	    //mise en place des résultats comptes Période N-1

	    //var_dump($MesResultatsCompteNMoins1Cumul);die();
	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, 'ENCADRE') !== false) {
			$Famille = str_replace("ENCADRE", "", $codePoste);
		} elseif (strpos($codePoste, 'Poste') !== false) {
			$MonCodePoste = str_replace("Poste", "", $codePoste);
		}
		

		
		elseif(is_numeric($codePoste))
		{
		    //c'est une ligne d'un compte
		    //echo StringHelper::NombreFr($MesResultatsCompte[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";

		    $reacompteNMois1 = $MesResultatsCompteNMoins1[$codePoste."UMois"]["BAL_BALANCE"];

		    if($Type ==  "Produits")
		    {
			$reacompteNMois1 = -$reacompteNMois1;
		    }
		    $MesResultats[$MonCodePoste."||#||"."UMoisAnneeMoinsUn"]["Montant"] += $reacompteNMois1;
		    $MesResultats[$MonCodePoste."||#||"."UMoisRealise"]["Famille"] = $Famille;


		    $UneLigneDb[] =  array(StringHelper::NombreFr($reacompteNMois1)=>array("align"=>"right"));

		    $UneLigneDb[] = "";//colone vide
		}
	    }


	    //mise en place des résultats comptes CUMUL Période N

	    //var_dump($MesResultatsCompte);
	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, 'ENCADRE') !== false) {
			$Famille = str_replace("ENCADRE", "", $codePoste);
		} elseif (strpos($codePoste, 'Poste') !== false) {
			$MonCodePoste = str_replace("Poste", "", $codePoste);
		}
		
		elseif(is_numeric($codePoste))
		{
		    //c'est une ligne d'un compte
		    //echo StringHelper::NombreFr($MesResultatsCompte[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";

		    $reacumul = $MesResultatsCompte[$codePoste."UMois"]["BAL_CUMUL"];
		    if($Type ==  "Produits")
		    {
			$reacumul = -$reacumul;
		    }
		    $MesResultatsCumul[$MonCodePoste."||#||"."UMoisRealise"]["Montant"] += $reacumul;
		    $MesResultatsCumul[$MonCodePoste."||#||"."UMoisRealise"]["Famille"] = $Famille;
		    $UneLigneDb[] =  array(StringHelper::NombreFr($reacumul)=>array("align"=>"right"));
		    $UneLigneDb[] = "";//colone vide
		}
	    }

	    //mise en place des résultats comptes CUMUL Période N-1
	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, 'ENCADRE') !== false) {
			$Famille = str_replace("ENCADRE", "", $codePoste);
		} elseif (strpos($codePoste, 'Poste') !== false) {
			$MonCodePoste = str_replace("Poste", "", $codePoste);
		}
		
		elseif(is_numeric($codePoste))
		{
		    //c'est une ligne d'un compte
		    //echo StringHelper::NombreFr($MesResultatsCompte[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";

		    $reacumulNMois1 = $MesResultatsCompteNMoins1Cumul[$codePoste."UMois"]["BAL_CUMUL"];

		    if($Type ==  "Produits")
		    {
			$reacumulNMois1 = -$reacumulNMois1;
		    }
		    $MesResultatsCumul[$MonCodePoste."||#||"."UMoisAnneeMoinsUn"]["Montant"] += $reacumulNMois1;
		    $MesResultatsCumul[$MonCodePoste."||#||"."UMoisRealise"]["Famille"] = $Famille;
		    $UneLigneDb[] =  array(StringHelper::NombreFr($reacumulNMois1)=>array("align"=>"right"));
		}
	    }
	}
	elseif($Periode && $resume)
	{
	    $MesResultatsPeriode = dbAcces::getResultatsCompteLieu(array(
		"BAL_MOIS_DEB"	=>$Periode["BAL_MOIS_DEB"],
		"BAL_MOIS_FIN"	=>$Periode["BAL_MOIS_FIN"],
		"LIE_NUM"	=>$_SESSION["inLIE_NUM"],
		"codePoste"	=>$codePosteSelect
	    ));

	    //mise en place des résultats comptes CUMUL Période N-1
	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		if(is_numeric($codePoste))
		{
		    //c'est une ligne d'un compte
		    //echo StringHelper::NombreFr($MesResultatsCompte[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";

		    $Rea = $MesResultatsPeriode[$codePoste]["BAL_BALANCE"];

		    if($Type ==  "Produits")
		    {
			$Rea = -$Rea;
		    }

		    $UneLigneDb[] =  array(StringHelper::NombreFr($Rea)=>array("align"=>"right"));
		}
	    }

	}

	//mise en place des résultats + totauxs + sTotaux

	//$MesResultats = dbAcces::getResultatsPoste($MoisActuel,true,true,true,false,$codePosteSelect,$Type,$TypeCompte);

	if($MoisActuel)
	{

	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, 'Total') !== false) {
		    $codePoste = str_replace("Total","",$codePoste);

		    //c'est une ligne d'un poste
		    //echo StringHelper::NombreFr($MesResultats[$codeCompte."||#||"."UMoisRealise"]["Montant"])."<br/>";

		    $rea = $MesResultats[$codePoste."||#||"."UMoisRealise"]["Montant"];
		    //$prev = $MesResultats[$codePoste."||#||"."UMoisPrevu"]["Montant"];
		    $anm1 = $MesResultats[$codePoste."||#||"."UMoisAnneeMoinsUn"]["Montant"];

		    $Total["Total".$codePoste."||col||1"] = $rea;
		    $Total["Total".$codePoste."||col||3"] = $anm1;
		    //$UneLigneDb[] =  array(StringHelper::NombreFr($prev)=>array("align"=>"right"));
		    //$UneLigneDb[] =  array(StringHelper::NombreFr($anm1)=>array("align"=>"right"));

		    //echo "TOTAL".$MesResultats[$codeCompte."||#||"."UMoisRealise"]["Famille"]."||col||1"." = ".$MesResultats[$codeCompte."||#||"."UMoisRealise"]["Montant"]."<br/>";

		    $TotauxF["TOTAL".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||1"] += $rea;
		    //$TotauxF["TOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["Famille"]."||col||2"] += $prev;
		    $TotauxF["TOTAL".$MesResultats[$codePoste."||#||"."UMoisAnneeMoinsUn"]["Famille"]."||col||3"] += $anm1;

		    /*$TotauxF["Total".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||1"] += $rea;
				    //$TotauxF["TOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["Famille"]."||col||2"] += $prev;
				    $TotauxF["Total".$MesResultats[$codePoste."||#||"."UMoisAnneeMoinsUn"]["Famille"]."||col||2"] += $anm1;
		    */
		    $TotauxSF["STOTAL".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||1"] += $rea;
		    //$TotauxSF["STOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["SsFamille"]."||col||2"] += $prev;
		    $TotauxSF["STOTAL".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||3"] += $anm1;
		}
	    }
	}

	//var_dump($Total);

	//$MesResultats = dbAcces::getResultatsPoste($MoisActuel,true,false,true,true,$codePosteSelect,$Type,$TypeCompte);

	if(!$resume)
	{

	    //CUMULS
	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, 'Total') !== false) {
		    $codePoste = str_replace("Total","",$codePoste);
		    $rea = $MesResultatsCumul[$codePoste."||#||"."UMoisRealise"]["Montant"];
		    //$prev = $MesResultats[$codeCompte."||#||"."UMoisPrevu"]["Montant"];
		    $anm1 = $MesResultatsCumul[$codePoste."||#||"."UMoisAnneeMoinsUn"]["Montant"];

		    //$UneLigneDb[] = array(""=>array("class"=>"colvide"));;//colone vide

		    $Total["Total".$codePoste."||col||5"] = $rea;
		    //var_dump($Total["Total".$codePoste."||col||4"]);
		    $Total["Total".$codePoste."||col||7"] = $anm1;


		    //$UneLigneDb[] =  array(StringHelper::NombreFr($rea)=>array("align"=>"right"));
		    //$UneLigneDb[] =  array(StringHelper::NombreFr($prev)=>array("align"=>"right"));
		    //$UneLigneDb[] =  array(StringHelper::NombreFr($anm1)=>array("align"=>"right"));

		    $TotauxF["TOTAL".$MesResultatsCumul[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||5"] += $rea;
		    //$TotauxF["TOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["Famille"]."||col||6"] += $prev;
		    $TotauxF["TOTAL".$MesResultatsCumul[$codePoste."||#||"."UMoisAnneeMoinsUn"]["Famille"]."||col||7"] += $anm1;

		    $TotauxSF["STOTAL".$MesResultatsCumul[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||5"] += $rea;
		    //$TotauxSF["STOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["SsFamille"]."||col||6"] += $prev;
		    $TotauxSF["STOTAL".$MesResultatsCumul[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||7"] += $anm1;
		}
	    }
	}

	//Recup des écarts
	/*
		if($Ecarts = DetailChargeProd::getEcartCumul($MoisActuel,$Type,false,$TypeCompte))
		{
			foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb)
			{
				if(is_numeric($codeCompte))
				{
					//$UneLigneDb[] = array(""=>array("class"=>"colvide"));//colone vide
					//$UneLigneDb[] =  array(StringHelper::NombreFr($Ecarts[$codeCompte."||#||ReaPrev"])=>array("align"=>"right"));
					//$UneLigneDb[] =  array(StringHelper::NombreFr($Ecarts[$codeCompte."||#||ReaAnneeMoinUn"])=>array("align"=>"right"));

					$TotauxF["TOTAL".$MesResultats[$codeCompte."||#||"."UMoisRealise"]["Famille"]."||col||9"] += $Ecarts[$codeCompte."||#||ReaPrev"];
					$TotauxF["TOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["Famille"]."||col||10"] += $Ecarts[$codeCompte."||#||ReaAnneeMoinUn"];

					$TotauxSF["STOTAL".$MesResultats[$codeCompte."||#||"."UMoisRealise"]["SsFamille"]."||col||9"] += $Ecarts[$codeCompte."||#||ReaPrev"];
					$TotauxSF["STOTAL".$MesResultats[$codeCompte."||#||"."UMoisPrevu"]["SsFamille"]."||col||10"] += $Ecarts[$codeCompte."||#||ReaAnneeMoinUn"];

				}

			}
		}*/


	if($MoisActuel)
	{

	    $TotalCharges = NULL;

	    //TOTAUX
	    //echo "<pre>";
	    //var_dump($TotauxF);
	    $array1 = array(1=>"",2=>"",3=>"",4=>"",5=>"");

	    foreach($Total as $codeLigneTableau => $Valeur)
	    {
		//recherche de la ligne Stotal pour lui affecter les valeurs
		$tab = explode("||col||",$codeLigneTableau);
		$MaCleTab1 = $tab[0];
		$Position = $tab[1];

		if($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MaCleTab1 != "Total" && $MesLignesTableau[$MaCleTab1])
		{
		    $MaLigneTotalPoste = &$MesLignesTableau[$MaCleTab1];
		    $MaLigneTotalPoste[$Position] = array(StringHelper::NombreFr($Valeur)=>array("style"=>"background-color:#EEEEEE;border-top:1px dotted black;padding:3px","class"=>"lnstotal","align"=>"right"));//
		    $MtabTran = array_diff_key($array1,$MaLigneTotal);

		    foreach ($MtabTran as $cle => $v)
		    {
			$MaLigneTotal[$cle] = $v;
		    }
		    ksort($MaLigneTotalPoste);
		}
	    }

	    foreach($TotauxF as $codeLigneTableau => $Valeur)
	    {
		//var_dump($codeLigneTableau);
		//recherche de la ligne total pour lui affecter les valeurs
		$tab = explode("||col||",$codeLigneTableau);
		$MaCleTab1 = $tab[0];
		$Position = $tab[1];

		if($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])// pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
		{
		    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
		    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur)=>array("style"=>"font-weight:bolder;","align"=>"right"));
		    $MtabTran = array_diff_key($array1,$MaLigneTotal);

		    foreach ($MtabTran as $cle => $v)
		    {
			$MaLigneTotal[$cle] = $v;
		    }
		    ksort($MaLigneTotal);
		}
	    }
	    //$array1 = array(1=>"",2=>"",3=>"",4=>array(""=>array("class"=>"colvide")),5=>"",6=>"",7=>"",8=>array(""=>array("class"=>"colvide")),9=>"",10=>"");

	    foreach($TotauxSF as $codeLigneTableau => $Valeur)
	    {
		//recherche de la ligne Stotal pour lui affecter les valeurs
		$tab = explode("||col||",$codeLigneTableau);
		$MaCleTab1 = $tab[0];
		$Position = $tab[1];

		if($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])
		{
		    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];
		    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur)=>array("style"=>"font-weight:bolder;font-weight: bolder;background-color:#EEEEEE;border-top:1px dotted black;","align"=>"right"));
		    $MtabTran = array_diff_key($array1,$MaLigneTotal);

		    foreach ($MtabTran as $cle => $v)
		    {
			$MaLigneTotal[$cle] = $v;
		    }
		    ksort($MaLigneTotal);
		    
		    $TotalCharges[$Position] += $Valeur;
		}
	    }

	    //$array1 = array(1=>"",2=>"",3=>"",4=>array(""=>array("class"=>"colvide")),5=>"",6=>"",7=>"",8=>array(""=>array("class"=>"colvide")),9=>"",10=>"");
            
	    if(!$resume && $MoisActuel)
	    {
                //Ln vide
                $UneLigneTableau = NULL;
                $UneLigneTableau[] = array("&nbsp;"=>array("colspan"=>"8"));//Orel 03022010 colspan 6
                $MesLignesTableau["VIDE".$Type] = $UneLigneTableau;
                    
		$LnBigTotal = NULL;
		$LnBigTotal[0] = array("Total des $Type :"=>array("style"=>"font-weight:bolder;background-color:#7CB9E8;","align"=>"right"));

		for($i=1;$i<=7;$i++)
		{
		    
			$LnBigTotal[$i] = array(StringHelper::NombreFr($TotalCharges[$i])=>array("style"=>"font-weight:bolder;background-color:#7CB9E8;","align"=>"right"));
		}
		//var_dump($LnBigTotal);
		$MesLignesTableau[] = NULL;
		$MtabTran = array_diff_key($array1,$LnBigTotal);

		foreach ($MtabTran as $cle => $v)
		{
		    $LnBigTotal[$cle] = $v;
		}

		ksort($LnBigTotal);
		$MesLignesTableau["BIGTOTAL"] = $LnBigTotal;
	    }
	}
	return $MesLignesTableau;
    }

    static function setTabMarges($MesLignesTableau)
    {
	$Retour = NULL;
	$CodeDef = NULL;

	foreach($MesLignesTableau as $codeCompte => $MesTds)
	{
	    if(stristr($codeCompte,"TITRE"))
		$Retour["$codeCompte MARGE"] = "Marges";
	    elseif(stristr($codeCompte,"STOTAL"))		$Retour["$codeCompte MARGE"] = "sous total :";
	    elseif(stristr($codeCompte,"TOTAL"))
		$Retour["$codeCompte MARGE"] = "Total marges :";
	    else
		$Retour[$codeCompte."MARGE"] = $MesTds;
	}

	foreach($Retour as $code => $LnMarge)
	{
	    $MesLignesTableau[$code] = $LnMarge;
	}
	return $MesLignesTableau;
    }

    static function getEcartCumul($Mois,$Type,$codePoste = false,$TypeCompte)
    {
	$Retour = NULL;

	if($MesResultats = dbAcces::getResultatsPoste($Mois,true,true,true,true,$codePoste,$Type,$TypeCompte))
	{
	    foreach ($MesResultats as $codeCompteAvecDesc => $UnResultat)
	    {
		$exp = explode("||#||",$codeCompteAvecDesc);
		$DescLigne = $exp[1];
		$codeCompte = $exp[0];

		if($Type == "Charges" && $DescLigne == "UMoisRealise")
		{
		    $UnResultat["Montant"] = -$UnResultat["Montant"];
		}

		if($DescLigne == "UMoisRealise")
		{
		    $Retour[$codeCompte."||#||"."ReaPrev"] += $UnResultat["Montant"];
		    $Retour[$codeCompte."||#||"."ReaAnneeMoinUn"] += $UnResultat["Montant"];
		}
		elseif($DescLigne == "UMoisPrevu")      	$Retour[$codeCompte."||#||"."ReaPrev"] -= $UnResultat["Montant"];
		elseif($DescLigne == "UMoisAnneeMoinsUn")	$Retour[$codeCompte."||#||"."ReaAnneeMoinUn"] -= $UnResultat["Montant"];
	    }
	}
	return $Retour;
    }
}   ?>