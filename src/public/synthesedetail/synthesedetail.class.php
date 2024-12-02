<?php
use Helpers\StringHelper;
session_start();
include_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../Bilan/Bilan.class.php';

class SyntheseDetail
{
    static function getTab($Type,$MoisActuel,$CPTB_FAMILLE = false,$CPTB_SFAMILLE = false,$codePoste_synthese = false)
    {

	global $Imprimer;


	if(!$Periode["BAL_MOIS_DEB"] || !$Periode["BAL_MOIS_FIN"])
	    $Periode = NULL;

        $d = NULL;
        $d = array(
            "join" => " join comptes ON comptes.codeposte_synthese = compteposte_synthese.codePoste_synthese 
                        join compteposte on compteposte.codePoste = comptes.codePoste ",
            "index" => "codePoste"
            );
        if($codePoste_synthese)
            $d["tabCriteres"]["compteposte_synthese.codePoste_synthese"] = $codePoste_synthese;
	
	$MesPostes = dbAcces::getPosteSynthese($d);

	$MaCleTab = NULL;
	$TotauxF = NULL;
	$TotauxSF = NULL;
	$PremF = true;
	$PremSF = true;
	$FaireSToataux = true;


	//initialisation du tableau avec ligne total + stotal de chaque postes
	foreach($MesPostes as $codePoste => $UneLignePoste)
	{
	    
	    $UneLigneTableau = NULL;
	    

	    $d = NULL;
            $d = array(
                "join" => " join comptes ON comptes.codeposte_synthese = compteposte_synthese.codePoste_synthese 
                         join compteposte on compteposte.codePoste = comptes.codePoste ",
                "triRequete" => " ORDER BY comptes.numero ASC",
                "distinct * ",
                "index" => "code_compte"
                );
            
            $d["tabCriteres"]["compteposte.codePoste"] = $codePoste;

            $MesComptes = dbAcces::getPosteSynthese($d);

	    $MesResultatsCompte = dbAcces::getResultatsCompte($MoisActuel,false,false,false);

	    $MaSsFamille = explode("||#||",$UneLignePoste["SsFamille"]);
	    $MaSsFamille = $MaSsFamille[0];

	    if($UneLignePoste["SsFamille"] != $SsFamilleDef )
	    {  

		if($UneLignePoste["Famille"] == $FamilleDef)//ln nom sous-famille
		{

		    $UneLigneTableau[] =  array($MaSsFamille=>array("style"=>"padding: 5px;text-align:left; ","class"=>"tdfixe","width"=>"250"));

		    if($MoisActuel)
		    {
			
			    $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel)=>array("class"=>"tdfixe","width"=>"40","style"=>"border:none"));
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

		
		$UneLigneTableau = NULL;
		$UneLigneTableau[] =  array($MaSsFamille."<div class='div200' style='width:350px'></div>"=>array("style"=>"padding: 5px;text-align:left; ","class"=>"tdfixe","width"=>"250"));

                $UneLigneTableau[] = array(StringHelper::Mysql2DateFr($MoisActuel)."<div class='div70'></div>"=>array("class"=>"tdfixe","width"=>"40"));

		
		
		$MesLignesTableau["ENCADRE".$UneLignePoste["SsFamille"]] = $UneLigneTableau;
		$UneLigneTableau = NULL;

		$FamilleDef = $UneLignePoste["Famille"];
		$PremSF = true;
		$FaireSToataux = false;
	    }

	    $UneLigneTableauTitre = NULL;

	    $UneLigneTableauTitre[] = array(" - $toto".strtoupper(StringHelper::TextFilter($UneLignePoste["Libelle"]))=>array("align"=>"left","style"=>"font-weight:bolder;"));

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




	//***************************************/

	

	$UneLigneTableau = NULL;
	

	//mise en place des résultats comptes Période N

	if($MoisActuel)
	{
	    //var_dump($MesResultatsCompte);
	    foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codeCompte, "ENCADRE") !== false) {
			$Famille = str_replace("ENCADRE", "", $codeCompte);
		} elseif (strpos($codeCompte, "Poste") !== false) {
			$codePoste = str_replace("Poste", "", $codeCompte);
		}
		 elseif(is_numeric($codeCompte)){
		    //c'est une ligne d'un compte
		    $reacompte = $MesResultatsCompte[$codeCompte."UMois"]["BAL_CUMUL"];

		    

		    $MesResultats[$codePoste."||#||"."UMoisRealise"]["Montant"] += $reacompte;
		    $MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"] = $Famille;
		    $UneLigneDb[] =  array(StringHelper::NombreFr($reacompte)=>array("align"=>"right"));
		    
		}
	    }
	}


	//mise en place des résultats + totauxs + sTotaux

	if($MoisActuel)
	{

	    foreach ($MesLignesTableau as $codePoste => &$UneLigneDb)
	    {

		$codeCompte = str_replace("||#||ONFR", "", $codeCompte);

		if (strpos($codePoste, "Total") !== false) {
		    $codePoste = str_replace("Total","",$codePoste);

		    //c'est une ligne d'un poste
		    
		    $rea = $MesResultats[$codePoste."||#||"."UMoisRealise"]["Montant"];
		  
		    $Total["Total".$codePoste."||col||1"] = $rea;
		    
		    $TotauxF["TOTAL".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||1"] += $rea;
		    
		    $TotauxSF["STOTAL".$MesResultats[$codePoste."||#||"."UMoisRealise"]["Famille"]."||col||1"] += $rea;
		    
		}
	    }
	}

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
		    $MaLigneTotalPoste[$Position] = array(StringHelper::NombreFr($Valeur)=>array("style"=>"background-color:#EEEEEE;font-weight:bolder;border-top:1px dotted black;padding:3px","class"=>"lnstotal","align"=>"right"));//
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

	   
	}
	return $MesLignesTableau;
    }

}   


/**
 * Classe d'accès aux données de la table maTable
 * @author auteur
 * @since since
 * @name db_maTable
 */
class db_SyntheseDetail extends dbAcces
{
    
static function getPosteSynthese($d=NULL)
    {
        
	extract($d);

        $where = "";
        $tabRetour = NULL;

        //Boucle de construction du where
        foreach ($tabCriteres as $nomChamp => $valeur)
        {
            if(!is_array($valeur))
            {
                if (!$tabOP[$nomChamp])
                    $operation = " = "; //L'operation par défaut
                else
                    $operation = $tabOP[$nomChamp];

                $where .= " And " . $nomChamp . " " . $operation . " \"" . $valeur . "\" ";
            }
            elseif(isset($valeur["whereperso"]))
            {
                $where .= " AND ( ";
                $where .= $nomChamp.$valeur["whereperso"];
                $where .= " ) ";
            }
        }

        if(!$select)
            $select = " * ";

        //CONSTRUCTION DE LA REQUETE SQL
        $sql = "SELECT ".$select."
            FROM compteposte_synthese
            " . $join . "
            WHERE 1
            " . $where . "
            " . $triRequete . " ";
//echo $sql;
        $res = self::$db->sql_Query($sql);
        while ($ligne = self::$db->sql_FetchArray($res))
        {
            if(!$index)
                $tabRetour[$ligne["codePoste_synthese"]] = $ligne;
            else
            {
                if(!is_array($index))
                    $tabRetour[$ligne[$index]] = $ligne;
                else
                {
                    $kI = array_keys($index);
                    $k1 = $index[$kI[0]];
                    $k2 = $index[$kI[1]];
                    $k3 = $index[$kI[2]];

                    if(count($index) == 1)
                        $tabRetour[$ligne[$k1]] = $ligne;
                    elseif(count($index) == 2)
                        $tabRetour[$ligne[$k1]][$ligne[$k2]] = $ligne;
                    elseif(count($index) == 3)
                        $tabRetour[$ligne[$k1]][$ligne[$k2]][$ligne[$k3]] = $ligne;
                }
            }
        }

        return $tabRetour;

    }

}
    
    
   ?>