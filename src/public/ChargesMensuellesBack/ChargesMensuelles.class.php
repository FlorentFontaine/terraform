<?php

use Classes\DB\Database;
use Helpers\StringHelper;
use Repositories\PosteRepository;

include_once '../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../compChargesBack/compCharges.class.php';


class compChargesProdMensuel
{
	static $TableNumber = 0;
	
	static function display_EnteteTab($TitleTable,$Type,$cluster,$k,$MoisVoulu,$Intitule=NULL,$Imprimer=NULL)
	{

	    self::$TableNumber++;

	    $FREEZPLAN = 5;

        if ($Type == "Produits") {
            if (strpos($Intitule, 'MANDAT') !== false) {
                $OptionPlus = "FITHEIGHT:1;HEIGHT:37;";
            } else {
                $OptionPlus = "FITHEIGHT:1;HEIGHT:27;";
            }
        } else {
            $OptionPlus = "FITHEIGHT:1;HEIGHT:21;";
        }

	    if($cluster)
	    {
		$FREEZPLAN++;
		$ROWTOREPEAT++;
	    }


	    ?>


	    <table dir="IMP_PDF;TITLETABLE:<?php echo $TitleTable." - ".$Intitule; ?>;FREEZEPLAN:B<?php echo $FREEZPLAN; ?>;ORIENTATION:LANDSCAPE;<?php echo $OptionPlus; ?>" style="width:0px" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000" align="center" id="tab_Mensuel<?php echo self::$TableNumber ?>">
               <thead>
                <?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>$TitleTable,"colspanLeft"=>2,"colspanCenter"=>14,"colspanRight"=>4)); ?>
                <?php if($Intitule && !$Imprimer){ 
                //echo '<tr><td class="EnteteTab tdfixe" colspan="20" style="text-align:center;font-weight:bold;border:none">'.$Intitule.'</td></tr>';
                //echo '<tr><td class="tdfixe" colspan="20" style="text-align:center;font-weight:bold;border:none"></td></tr>';
                 } ?>
	    
	    <tr class="EnteteTab" style="font-size: 14px">
		<td width="170" class='tdfixe' style="font-size: 9px"><div class="div200"></div><?php //echo strtoupper($Type) ?></td>
		<?php

		if ($_SESSION["station_DOS_NBMOIS"]>12)
		{
			$NbCols = 13;
		}
		else
		{
		    if($print)
			$NbCols = 7;
		    else
			$NbCols = 13;//$_SESSION["station_DOS_NBMOIS"] +1;

		}



		for($i=$k; $i<$NbCols+$k-1  ;$i++)
		{

			$MaDateDebut = StringHelper::DatePlus($MoisVoulu,array("moisplus"=>-11));;
			$Date = StringHelper::DatePlus($MaDateDebut,array("dateformat"=>"Y-m-d","moisplus"=>+$i));
			$Date = StringHelper::DateSemiComplete($Date);
			echo "<td width='40' class='tdfixe' style='font-size: 9px'><div class='div60'></div> $Date </td>";
		}
		?>

		<td class="tdfixe colvide" width="1"></td>

		<td width="40" class="tdfixe" style='font-size: 9px'>R&eacute;alis&eacute;<div class="div60"></div></td>
		<td width="40" class="tdfixe" style='font-size: 9px'>Pr&eacute;vu<div class="div60"></div></td>
		<td width="40" class="tdfixe" style='font-size: 9px'>N-1<div class="div60"></div></td>
		<td class="tdfixe colvide" width="1"></td>

		<td width="50" class="tdfixe" style='font-size: 9px'>
                    R&eacute;al.<br/> Pr&eacute;v.
                    <div class="div60"></div>
                </td>
		<td width="50" class="tdfixe" style='font-size: 9px'>
                    R&eacute;al.<br/> (N-1)
                    <div class="div60"></div>
                </td>


	    </tr>
               </thead><tbody>
	    <?php
	}

	static function getTab($Type,$MoisActuel,$FamilleSelect = false,$SsFamilleSelect = false,$codePosteSelect = false,$Produits=NULL,$print=NULL,$cluster=false)
	{
		
		$TypeCompte = array("test"=>"!=","type"=>"achat");//pour la r?cup?ration des r?sultat avec tout les compte de la balance sauf les compte d'achat

		
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
		
		$tri = array(
			"ordre" => "ASC"
		);
		
		$Genre = $Type;
		
        $posteRepository = new PosteRepository();
        $MesPostes= $posteRepository->getPosteByType($Type);
		
		
		$MesLignesTableau = &compChargesProd::IniTableau($MesPostes,false,true,false,true,false,13,true);
		
		//mise en place des r?sultats + totauxs + sTotaux
		
		
		
		if($_POST["btn_avant"])
		{
			$k = $_POST["plus"];
			$k++;
		}
                elseif($_POST["btn_arriere"])
		{
			$k = $_POST["plus"];
			$k--;
		}
		else
		{
			$k=0;
		}
		
		/*if ($_SESSION["station_DOS_NBMOIS"]>12)
		{
			$NbCols = 13;
		}
		elseif($print)
		{	
			$NbCols = 7;
		}
		else
		{
                    $NbCols = $_SESSION["station_DOS_NBMOIS"] +1;
		}
		
		if($_GET['print'])	
		$NbCols = 7;
                */
                $NbCols = 13;

                $TotalProduits = NULL;
                
                $MaDateDebut = StringHelper::DatePlus($MoisActuel,array("moisplus"=>-11,"dateformat"=>"Y-m-00"));

                $MesResultatsAll = dbAcces::getResultatsPoste(array("debut"=>$MaDateDebut,"fin"=>$MoisActuel),true,true,true,false,$codePosteSelect,$Type,array("test"=>"!=","type"=>"achat"),false,false,false,$contenu,$cluster);

		for($i=$k; $i<$NbCols-1+$k ;$i++)
                {

                    
                    $MoisActuel = StringHelper::DatePlus($MaDateDebut,array("dateformat"=>"Y-m-00","moisplus"=>+$i));
		    
                    //$MesResultats = dbAcces::getResultatsPoste($MoisActuel,true,true,true,false,$codePosteSelect,$Type,array("test"=>"!=","type"=>"achat"),false,false,false,&$contenu,$cluster);
		    
                    $MesResultats = $MesResultatsAll[$MoisActuel];

                    $carb = dbAcces::getLitrageCarb($_SESSION["station_DOS_NUM"],$MoisActuel,false,false);

                    $MesMarges = dbAcces::getMargeTheoriqueGroupe($MoisActuel,$_SESSION["station_DOS_NUM"],false,false,false,$cluster);
                    

                    foreach ($MesLignesTableau as $codeCompte => &$UneLigneDb)
                    {

                            if(is_numeric($codeCompte))
                            {
                                if($MesPostes[$codeCompte]["Famille"] == "Carburants")
                                {
                                    $rea[$codeCompte] = $carb[$MesPostes[$codeCompte]["CARB_NUM"]]["CARV_VOLUME"]/1000;
                                }
                                else
                                {
                                    if($Type == "Charges") {
                                        $rea[$codeCompte] = $MesResultats[$codeCompte."||#||UMoisRealise"]["Montant"];
                                    } else {
                                        $rea[$codeCompte] = -$MesResultats[$codeCompte."||#||UMoisRealise"]["Montant"];
                                    }
                                }
                                
                                $UneLigneDb[] =  array(StringHelper::NombreFr($rea[$codeCompte])=>array("align"=>"right"));

                                $j=$i - $k + 1;


                                $TotauxF["TOTAL".$MesPostes[$codeCompte]["Famille"]."||col||".$j] += $rea[$codeCompte];
                                $TotauxSF["STOTAL".$MesPostes[$codeCompte]["SsFamille"]."||col||".$j] += $rea[$codeCompte];
                                $TotChargesMens[$j] = $TotalCharges["Total"]  ;
                                $Ecart[$j] = $ResultatEcart;
                                $StockReel[$j] = $VerifStockReel;

                                if($MesPostes[$codeCompte]["Resultat"] && ($MesPostes[$codeCompte]["Famille"] != "VENTES MARCHANDISES" && (($MesPostes[$codeCompte]["Famille"] != "ONFR" && $Type =="Produits") || $Type == "Charges"))) {
                                    $TotalProduits[$j] +=  $rea[$codeCompte];
                                }

                                $rea[$codeCompte] = NULL;
                            } elseif (stripos($codeCompte, 'CMARGE') !== false) {
                                $codeLigne = $codeCompte;
                                $codeCompte = str_ireplace('CMARGE', '', $codeCompte);
                            
                                $MonEcartMarge = dbAcces::getEcartMarge($_SESSION["station_DOS_NUM"], $MoisActuel, $codeCompte);
                            
                                $rea[$codeCompte] = $MesMarges[$codeCompte]["Montant"] + $MonEcartMarge;
                            
                                $UneLigneDb[] =  array(StringHelper::NombreFr($rea[$codeCompte]) => array("align" => "right"));
                            
                                $j = $i - $k + 1;
                            
                                $TotauxF["MARGETOTAL" . $MesPostes[$codeCompte]["Famille"] . "||col||" . $j] += $rea[$codeCompte];
                                $TotauxSF["MARGESTOTAL$PlusTitreONFR" . $MesPostes[$codeCompte]["SsFamille"] . "||col||" . $j] += $rea[$codeCompte];
                            
                                if ($MesPostes[$codeCompte]["Famille"] == "ONFR" && $Type == "Produits") {
                                    $TotauxSF["STOTALRES_ONFR" . $MesPostes[$codeCompte]["SsFamille"] . "||col||" . $j] += $rea[$codeCompte];
                                }
                            
                                $TotChargesMens[$j] = $TotalCharges["Total"];
                                $Ecart[$j] = $ResultatEcart;
                                $StockReel[$j] = $VerifStockReel;
                            
                                $TotalProduits[$j] +=  $rea[$codeCompte];
                            
                                $rea[$codeCompte] = NULL;
                            } elseif (stripos($codeCompte, 'CH_ONFR') !== false) {
                                $SsFamille = str_ireplace('CH_ONFR', '', $codeCompte);
                            
                                $MesResultatFamille = dbAcces::getResultatsPoste($MoisActuel, true, false, false, false, false, "Charges", false, false, $SsFamille);
                            
                                $MonResultat = NULL;
                            
                                foreach ($MesResultatFamille as $key => $Values) {
                                    $MonResultat["rea"] += $Values["Montant"];
                                }
                            
                                $UneLigneDb[] = array(StringHelper::NombreFr($MonResultat["rea"]) => array("align" => "right"));
                            
                                $TotauxSF["STOTALRES_ONFR" . $SsFamille . "||col||" . $j] -= $MonResultat["rea"];
                            }
                            
                            
                    }

                    foreach($TotauxF as $codeLigneTableau => $Valeur)
                    {
                            //recherche de la ligne total pour lui affecter les valeurs
                            $tab = explode("||col||",$codeLigneTableau);

                            $MaCleTab1 = $tab[0];
                            $Position = $tab[1];

                            if($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])// pas egal a cela tout seul mais peut etre "TOTAL charge de ..."
                            {

                                    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                                    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur)=>array("align"=>"right"));

                                    $MtabTran = array_diff_key($array1,$MaLigneTotal);


                                    foreach ($MtabTran as $cle => $v)
                                    {

                                            $MaLigneTotal[$cle] = $v;
                                    }
                                    ksort($MaLigneTotal);

                                    
                                    
                            }
                    }

                    
                

		    //var_dump($TotauxSF["MARGESTOTALONFRLavage||col||2"]);

                    foreach($TotauxSF as $codeLigneTableau => $Valeur)
                    {
                            //recherche de la ligne Stotal pour lui affecter les valeurs
                            $tab = explode("||col||",$codeLigneTableau);

                            $MaCleTab1 = $tab[0];

                            $Position = $tab[1];

			    
			    
                            if($MaCleTab1 != "TOTAL" && $MaCleTab1 != "STOTAL" && $MesLignesTableau[$MaCleTab1])
                            {
				

                                    $MaLigneTotal = &$MesLignesTableau[$MaCleTab1];

                                    $MaLigneTotal[$Position] = array(StringHelper::NombreFr($Valeur)=>array("align"=>"right"));

                                    $MtabTran = array_diff_key($array1,$MaLigneTotal);


                                    foreach ($MtabTran as $cle => $v)
                                    {

                                            $MaLigneTotal[$cle] = $v;
                                    }
                                    ksort($MaLigneTotal);


                            }
                    }
		    
		}
		
		

                
                //$MesResultats = db_compChargesProdMensuel::getResultatCumul($_SESSION["station_DOS_NUM"],$_SESSION["station_STA_NUM"],$MoisActuel,$Type);



		//$array1 = array(1=>"",2=>"",3=>"",4=>array(""=>array("class"=>"colvide")),5=>"",6=>"",7=>"",8=>array(""=>array("class"=>"colvide")),9=>"",10=>"");
		
		$LnBigTotal = NULL;
		
		$LnBigTotal[0] = array("Total des $Type :"=>array("libelle"=>"1","style"=>"font-weight:bolder;","align"=>"right"));
		
		for($i=1; $i<$NbCols  ;$i++)
                {

                    $LnBigTotal[$i] = array(StringHelper::NombreFr($TotalProduits[$i])=>array("style"=>"font-weight:bolder;","align"=>"right"));

                }
		
		$MesLignesTableau[] = NULL;
		
		
		
		$MesLignesTableau["BIGTOTAL"] = $LnBigTotal;
		
		
		if($Type == 'Produits')
		{
					
			$LnResultat[0] = array("RESULTAT :"=>array("libelle"=>"1","style"=>"font-weight:bolder;","align"=>"right"));
			
			
			for($i=1; $i<$NbCols  ;$i++)
                        {

                            
                            $MoisActuel = StringHelper::DatePlus($MaDateDebut,array("dateformat"=>"Y-m-00","moisplus"=>+$i-1+$k));


                            $MesResultatsCharges = db_compChargesProdMensuel::getResultatMensuel($_SESSION["station_DOS_NUM"],$_SESSION["station_STA_NUM"],$MoisActuel,"Charges",false,true,FALSE,FALSE,$cluster);
                            //var_dump($MesResultatsCharges);

                            foreach ($MesResultatsCharges as $key => $value)
                                break;

                            //echo $MoisActuel." => ".$MesResultatsCharges[$key]["Montant"]."<br/>";

                            $ResultatReel[$i] = $TotalProduits[$i] - ($TotChargesMens[$i]) + $Ecart[$i] - $MesResultatsCharges[$key]["Montant"];

                            if($TotalProduits[$i])
                                $LnResultat[$i] = array(StringHelper::NombreFr($ResultatReel[$i])=>array("style"=>"font-weight:bolder;","align"=>"right"));
                            else
                                $LnResultat[$i] = array(""=>"");

                            
                        }
			
                        
			$MesLignesTableau["TOTALRESULTAT"] = $LnResultat;
		}
		
                //si la dernière famille à le même nom que les type(charges ou produits), on n'affiche pas ce total
                unset($MesLignesTableau["TOTALCharges"]);
                
                unset($MesLignesTableau["TITRECharges"]);
                unset($MesLignesTableau["TITREVENTES MARCHANDISES"]);
                
                unset($MesLignesTableau["TITREPOURTITREVENTES MARCHANDISES"]);
                
                //var_dump($MesLignesTableau);
                
		return $MesLignesTableau;
		
	}
	
	
	
	
}

class db_compChargesProdMensuel extends dbAcces
{
    static function getResultatMensuel($DOS_NUM=NULL,$codeStation,$Periode,$Type,$cumul,$all = false,$Famille=false,$SsFamille=false,$cluster=false)
    {
            $whereplus = "";
            $group = "";
            $groupRGD = "";

            if($DOS_NUM)
                $Dossier = "and dossier.DOS_NUM = '$DOS_NUM'";

            if($cluster)
                $Dossier = " and station.STA_NUM_CLUSTER = '".$_SESSION["station_STA_NUM_CLUSTER"]."' ";

            if($cumul)
                $whereplus .= " and BAL_MOIS <= '$Periode' ";
            else
                $whereplus .= " and BAL_MOIS = '$Periode' ";


            if($cumul)
                $whereplusRGD .= " and RGD_MOIS <= '$Periode' ";
            else
                $whereplusRGD .= " and RGD_MOIS = '$Periode' ";

            
            $group = "group by compteposte.`codePoste`";
            

            if($Famille)
                $whereplus .= " and compteposte.Famille = '$Famille' ";

            if($SsFamille)
                $whereplus .= " and compteposte.SsFamille = '$SsFamille' ";
            

            $whereplus .= " and comptes.MargeDirect = 0 ";

            if(($Famille && $Famille != "VENTES MARCHANDISES") || ($Type == "Charges"))
                $whereplusRGD .= " and 0 ";


            if($all)
            {
                $SelectSumDeb = "select '0' as codePoste,sum(Montant) as Montant from (";
                $SelectSumFin = ") MesMontant";
            }

            $sql = "

            $SelectSumDeb

            select compteposte.`codePoste`,sum(BAL_BALANCE) as Montant  from balance
            join comptes on comptes.code_compte = balance.`codeCompte`
            join compteposte on compteposte.`codePoste` = comptes.`codePoste`
            join dossier on dossier.DOS_NUM = balance.DOS_NUM
            join station on station.STA_NUM = dossier.STA_NUM

            where 1 and comptes.`Type` != 'achat' and compteposte.`Type` = '$Type'

            $whereplus $Dossier
            $group

            UNION ALL

            select '17',sum(-RGD_MONTANT) as Montant from rgdivers
            join dossier on dossier.DOS_NUM = rgdivers.DOS_NUM
            join station on station.STA_NUM = dossier.STA_NUM

            where 1
            $whereplusRGD $Dossier
            and RGD_CHAMP = 'pressecom'
            $groupRGD

            UNION ALL

            select '23',sum(-RGD_MONTANT) as Montant from rgdivers
            join dossier on dossier.DOS_NUM = rgdivers.DOS_NUM
            join station on station.STA_NUM = dossier.STA_NUM
            where 1
            $whereplusRGD $Dossier
            and RGD_CHAMP = 'distriauto'

            $SelectSumFin
            
            ";

            //echo $sql;die();

            $res = Database::query($sql);

            $Return = NULL;

            while($ln = Database::fetchArray($res))
            {
                if($Type == "Produits")
                    $ln["Montant"] = -$ln["Montant"];

                $ln["Montant"] = round($ln["Montant"]);
                
                //if(!$Return[$ln["codePoste"]] || $ln["codePoste"] == 17)
                $Return[$ln["codePoste"]] = $ln;

                

            }
            return $Return;
    }

    static function getResultatCumul($DOS_NUM=NULL,$codeStation,$Periode,$Type)
    {
            $whereplus = "";
            $group = "";


            $Dossier = "and `DOS_NUM` = '$DOS_NUM'";
            $group = "group by compteposte.`codePoste`";

            $sql = "select compteposte.`codePoste`,sum(BAL_BALANCE) as Montant  from balance
            join comptes on comptes.code_compte = balance.`codeCompte`
            join compteposte on compteposte.`codePoste` = comptes.`codePoste`

            where 1 and comptes.`Type` != 'achat' 
            and BAL_MOIS <= '$Periode'
            and compteposte.`Type` = '$Type'
            $whereplus $Dossier
            $group
            ";


            $res = Database::query($sql);

            $Return = NULL;

            while($ln = Database::fetchArray($res))
            {
                if($Type == "Produits")
                    $ln["Montant"] = -$ln["Montant"];

                $Return[$ln["codePoste"]] = $ln;

            }
            return $Return;
    }
}



?>
