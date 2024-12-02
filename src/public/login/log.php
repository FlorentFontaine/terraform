<?php

use Classes\DB\Database;

require_once '../dbClasses/AccesDonnees.php';

$_SESSION["logedVar"] = false;
$_SESSION["loged"] = false;
$_SESSION["station_STA_NUM"] = false;
$_SESSION["station_DOS_NUM"] = false;
$_SESSION["agip_AG_NUM"] = false;
$_SESSION["MoisHisto"] = false;
$_SESSION["sarl_liste_CAB_NUM"] = false;
$_SESSION["sarl_liste_LIE_NUM"] = false;
if (!$_POST) { $_POST = $_GET; }

$reqLogin = "
select 'station' from station where STA_MAIL = '".addslashes($_POST['login'])."' and STA_MDP = '".addslashes($_POST['mdp'])."' 
union
select 'comptable' from comptable where CC_MAIL = '".addslashes($_POST['login'])."' and CC_MDP = '".addslashes($_POST['mdp'])."'
union
select 'agip' from agip where AG_MAIL = '".addslashes($_POST['login'])."' and AG_MDP = '".addslashes($_POST['mdp'])."'
union
select 'cds' from chefSecteur where E_Mail = '".addslashes($_POST['login'])."' and Mot_de_passe = '".addslashes($_POST['mdp'])."'
union
select 'cdr' from chefRegion where E_Mail = '".addslashes($_POST['login'])."' and Mot_de_passe = '".addslashes($_POST['mdp'])."'
union
select 'cdv' from chefVente where E_Mail = '".addslashes($_POST['login'])."' and Mot_de_passe = '".addslashes($_POST['mdp'])."' ";

//echo $reqLogin;

$resLogin = Database::query($reqLogin);


$ligneLogin = Database::fetchArray($resLogin);

//var_dump($ligneLogin);
switch($ligneLogin['station'])
{
	case "comptable":
		{
			$sql = "select comptable.*,cabinet.*,balanceformat.* from comptable join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM
			LEFT join balanceformat on balanceformat.BAF_NUM = cabinet.BAF_NUM where CC_MAIL = '".$_POST["login"]."' and CC_MDP='".$_POST["mdp"]."'";
			
			break;
		}
	case "agip":
	{
		$sql = "select agip.* from agip where AG_MAIL = '".$_POST["login"]."' and AG_MDP='".$_POST["mdp"]."'";
		break;
	}
	
	case "cds":
	{
		$sql = "select * from chefSecteur where E_Mail = '".$_POST["login"]."' and Mot_de_passe='".$_POST["mdp"]."'";
		break;
	}
	case "cdr":
	{
		$sql = "select * from chefRegion where E_Mail = '".$_POST["login"]."' and Mot_de_passe='".$_POST["mdp"]."'";
		break;
	}
	case "cdv":
	{
		$sql = "select * from chefVente where E_Mail = '".$_POST["login"]."' and Mot_de_passe='".$_POST["mdp"]."'";
		break;
	}
	
	case "station":
	{
		$sql = "select station.* from station  where STA_MAIL = '".$_POST["login"]."' and STA_MDP='".$_POST["mdp"]."'";
		break;
	}
}
$_POST["loguser"] = $ligneLogin['station'];
$res = Database::query($sql);
	
if($ln = Database::fetchArray($res))
{	
	
	$_SESSION["loged"] = $_POST["loguser"];
	$_SESSION["logedVar"] = $ln;
	header("Location:../StationBack/Liste.php");
	exit();
	?>
	<script type="text/javascript">
	window.open("../StationBack/Liste.php","IOReport","width=1280,height=50000,left=0,top=0,directories=no" ); 
	window.location.href = "../index_old.php";
	</script>
	<?php
	/*header("Location:../login/login.php");
	exit();*/
}
else
{
	$logerror = true;
}

?>