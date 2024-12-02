<?php

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../ExportBack/export.class.php';

$filename = "MyReport_" . $_SESSION["station_STA_NOM"] . "_" . date("m_Y", strtotime(str_replace("-00", "-01", $_SESSION["MoisHisto"])));

header("Content-type: application/msexcel");
header("Content-disposition: attachment; filename=" . $filename . ".xls");

Export::station();
Export::rensTaux();
Export::carb();
Export::divers();
Export::cle();
Export::charge();
Export::produit();
Export::marge();
Export::prev("Charges");
Export::prev("Produits");

echo "flag_fin\r\n";
