<?php
session_start();
include_once 'session.php';
include_once '../ctrl/ctrl.php';
require_once '../dbClasses/AccesDonnees.php';
require_once '../dbClasses/station.php';

if(!$MonCodeStation)
	return false;
	
$option["where"] = array("and"=>array("STA_CODECLIENT"=>" = '$MonCodeStation'"));	

if($MaStation = station::GetStation(false,$option))
{
	foreach ($MaStation as $STA_NUM => $Ln)
		break;
		
	//echo "Location : ../StationBack/open.php?STA_NUM=".$STA_NUM;
	header("Location:../StationBack/open.php?STA_NUM=".$STA_NUM);
	exit();
}

header("Location:".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);





?>