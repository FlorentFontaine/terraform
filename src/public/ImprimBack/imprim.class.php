<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

class Impression{
	private static $Entete;
	private static $Pied;

	static function Entete(){
		if(!Impression::$Entete){
			$EntetePiedFalse = true;
			// $impression = true;
			Impression::$Entete = true;	?>
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
				<link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
				<title>My Report</title>
                <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
				

			</head>
                        <body style="background: none">

                          <!--<input type="button" onclick="ReLoadPile()" value="suivant"/>  -->

			<div style="width: 850px;height:0px;border:0px"></div>
			<?php
			$Wait = true;
                        $CloseWindow = true;

                        if($_SESSION['formatImpression'] == "HTML"){ $impression = true; }
			include("../include/entete.inc.php");
                        ?>

                        <style type="text/css">
                            #tetepage
                            {
                                display:none;
                            }
                            .submit
                            {
                                visibility: hidden;
                            }
                            .titresection
                            {
                                    position: inherit;
                                    text-align:center;
                                    padding:3px;
                                    width:98%;
                                    margin:10px;
                                    font-family:  Arial, Helvetica, sans-serif;
                                    font-weight:bolder;
                                    font-size:14px;
                                    background-color:#E2E2E2;
                                    border-top:1px solid black;
                                    border-bottom:1px solid black;
                            }
                            .tabMenu
                            {
                                display: none;
                            }
                            

                        </style>


                        <?php
		}
		Impression::TeteStation();
	}

	static function TeteStation($logo = true,$CRR = false){	?>
		<table style="font-size: 7px;width:90%;border: 1px solid black;text-align: left" align="center">
		<tr><?php
		if($logo){	?><td rowspan="2" align="center" valign="middle"><img src="../images/iologo.gif" width="30" /></td><?php    }

		if(!$CRR){	?>
			<td style="width: 100px;" class="bolder" align="left">Soci&eacute;t&eacute; / PDV : </td>
			<td><?php echo $_SESSION["station_STA_SARL"]." / ".$_SESSION["station_LIE_NOM"]; ?></td><?php  
		}else{ ?>
			<td style="width: 100px;" class="bolder" align="left">Nom PDV : </td>
			<td><?php echo $_SESSION["station_LIE_NOM"]; ?></td><?php 
		} ?>

		<!-- <td style="width: 100px;padding-left: 20px " class="bolder" align="left">SARL : </td><td><?php echo $_SESSION["station_STA_SARL"]; ?></td>-->
		
		<td style="width: 100px;padding-left: 20px " class="bolder" align="left">Code station : </td>
		<td><?php echo $_SESSION["station_LIE_CODE"]; ?></td>

		<td style="width: 100px;padding-left: 20px " class="bolder" align="left" >Code soci&eacute;t&eacute; : </td>
		<td><?php echo $_SESSION["station_STA_CODECLIENT"]; ?></td>

		<?php if($_SESSION["NbAno"] && !$CRR) { ?>
			<td rowspan="2" class="bolder" style="color:red"> [ <?php echo $_SESSION["NbAno"]; ?> anomalie(s) ] </td>
		<?php } 

		if($CRR){	?>
			<td style="width: 100px;" class="bolder" align="left">Nom soci&eacute;t&eacute; : </td>
			<td><?php echo $_SESSION["station_STA_SARL"]; ?></td>
			<td style="width: 100px;" class="bolder" align="left">Nom Cabinet : </td>
			<td><?php echo $_SESSION["station_CAB_NOM"]; ?></td>
			<td class="bolder">Status g&eacute;rance : </td>
			<td style="text-align:right">&nbsp;<?php echo $_SESSION["station_STA_STATUS"]; ?></td>
			<td class="bolder"  style="width:25%">Type g&eacute;rance  : </td>
			<td  style="width:25%;text-align:right">&nbsp;<?php echo $_SESSION["station_STA_TYPEGER"]; ?></td>
			<td class="bolder"  style="width:25%">color</td>
			<td  style="width:25%;text-align:right"><?php
				if($_SESSION["ioreport_Bench_POST"]["MoisFinBench"] == date("Y-m-00",strtotime($_SESSION["station_DOS_FINEX"]))){
					$MaDateImport = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"],date("Y-m-00",strtotime($_SESSION["station_DOS_FINEX"])));
					global $colorSituation;
					echo $colorSituation[$MaDateImport[0]["BALI_TYPE"]]["num"];
				}	?>
			</td>
		<?php } ?>

		</tr><tr>
			<?php	if($logo){	}	?>
			<td style="width: 100px; " class="bolder" align="left">Debut exercice :</td>
			<td><?php echo StringHelper::MySql2DateFr($_SESSION["station_DOS_DEBEX"]); ?></td>
			<td style="width: 100px;padding-left: 20px " class="bolder" align="left" >Fin exercice :</td>
			<td ><?php echo StringHelper::MySql2DateFr($_SESSION["station_DOS_FINEX"]); ?></td>
			<td style="width: 100px;padding-left: 20px " class="bolder" align="left">P&eacute;riode de traitement :</td>
			<td align="left" ><?php echo StringHelper::MySql2DateFr($_SESSION["MoisHisto"]); ?></td>
		</tr>
		</table><?php
	}

	static function Pied(){
		if(!Impression::$Pied){
			Impression::$Pied = true;   $EntetePiedFalse = true;
			include("../include/pied.inc.php");	?>
			</body></html><?php
		}
	}

	static function Intermediaire(){	?><div class="breakafter">&nbsp;</div><?php   }

	static function Garde(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$Imprimer = true;

		include('../GardeBack/Garde.php');
		
	}

	static function Renseignement(){
            global $Imprimer;
		$EntetePiedFalse = true;
                $Imprimer = true;
		Impression::Entete();

		include('../RenseignementBack/Liste.php');
	}

	static function Balance(){
		$EntetePiedFalse = true;
		$Imp = true;
		Impression::Entete();

		include('../BalanceBack/Liste.php');
	}

	static function Mensuel($display,$cluster = false){
            global $Imprimer;

		$EntetePiedFalse = true;
		Impression::Entete();
		$mensuel = true;
		$Imprimer = true;
		if($cluster)
		    $_GET["cluster"] = 1;
                
		include('../compChargesBack/Liste.php');

		$_GET["cluster"] = 0;
		$mensuel = false;
		return $MesLignes;
	}

	static function DetailCharge(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["Produits"] = false;
                $Imprimer = true;
                
		include('../ChargesDetailBack/Liste.php');
	}

	static function Cumul($display){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$cumul = true;
		$Imprimer = true;

		include('../compChargesBack/Liste.php');
		
		$cumul = false;
		return $MesLignes;
	}

	static function DetailProduit(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["Produits"] = true;
                $Imprimer = true;

		include('../ChargesDetailBack/Liste.php');
	}

        static function CompProduit($cluster = false){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["Produits"] = true;
                $_GET["Charges"] = false;
		$Imprimer = true;
		if($cluster)
		    $_GET["cluster"] = 1;

		include('../ChargesMensuellesBack/Liste.php');
		$_GET["cluster"] = 0;

	}

        static function CompCharge($cluster = false){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["Produits"] = false;
                $_GET["Charges"] = true;
		$Imprimer = true;
		if($cluster)
		    $_GET["cluster"] = 1;

		include('../ChargesMensuellesBack/Liste.php');
		$_GET["cluster"] = 0;
	}

        static function synthese($cluster = false){
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["cumul"] = true;
		if($cluster)
		    $_GET["cluster"] = 1;

		include('../synthese/Liste.php');
		$_GET["cluster"] = 0;
	}

	static function Anomalie(){
		$EntetePiedFalse = true;
		Impression::Entete();

		include('../Anomalie/MAnomalie.php');
	}

        static function Clignotants(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$Imprimer = true;

		include('../Clignotant/MListe.php');
	}

	static function Marge(){
		$EntetePiedFalse = true;
		Impression::Entete();

		include('../MargeBack/Liste.php');
	}

	static function Bilan($cluster){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		if($cluster)
		    $_GET["cluster"] = 1;
                $Imprimer = true;
		include('../Bilan/Liste.php');
		$_GET["cluster"] = 0;
	}

	

	static function TGControle(){
            global $Imprimer;
		$Imprimer = true;
		$EntetePiedFalse = true;
		Impression::Entete();	//$_GET["Produits"] = false;

		include('../TGControle/index.php');
	}

	static function CRR(){
            global $Imprimer;
		$Imprimer = true;
		$EntetePiedFalse = true;
		Impression::Entete();	//$_GET["Produits"] = false;

		include('../CRR/CRR.php');
	}


        static function Prev(){
		$EntetePiedFalse = true;
		Impression::Entete();

		include('../PrevBack/Liste.php');
	}

        static function Prev_Shell(){
		$EntetePiedFalse = true;
		Impression::Entete();

                $SCP_TYPE = "Produits";

		include('../shell_prev/index.php');

                Impression::Intermediaire();

                $SCP_TYPE = "Charges";

		include('../shell_prev/index.php');

                Impression::Intermediaire();
                $SCP_TYPE = "ONFR";

		include('../shell_prev/index.php');

                
	}

        static function massesalariale(){
		$EntetePiedFalse = true;
		Impression::Entete();
                
		include('../MasseSalarialeBack/Liste.php');
	}

        static function objectif($param1=false,$param2=false){
		$EntetePiedFalse = true;
		Impression::Entete();

                $_GET["param1"] = $param1;
                $_GET["param2"] = $param2;

		include('../ObjectifSARL/index.php');
	}



	/*static function PrevProduit(){
		$EntetePiedFalse = true;
		Impression::Entete();
		$_GET["Produits"] = true;

		include('../PrevBack/Liste.php');
	}

        static function PrevCharge(){
		$Imprimer = true;
		$EntetePiedFalse = true;
		Impression::Entete();	//$_GET["Produits"] = false;

		include('../PrevBack/Liste.php');
	}*/

	static function Projection(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$Imprimer = true;
		
		include('../Projection/Liste.php');
	}

	static function ObjectifSARL(){
            global $Imprimer;
		$EntetePiedFalse = true;
		Impression::Entete();
		$Imprimer = true;

		include('../ObjectifSARL/index.php');
	}
}	?>