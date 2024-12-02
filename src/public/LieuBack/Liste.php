<?php

use App\htmlClasses\TableV2;
use Repositories\LieuRepository;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$notselect = true;

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../include/Filtres.php';
require_once __DIR__ . '/../htmlClasses/TableV2.php';

// Définition des clés de SESSION des champs pour lesquels il y a des filtres sur le page
$expectedFields = ['CAB_NUM', 'codeChefSecteur', 'inactif'];
Filtres::initFieldsValue($expectedFields);

// Requête pour avoir la liste des PDV
$listeLieuQuery = (new LieuRepository())->getLieuQuery();

// Construction du tableau pour l'affichage
$tableau = (new TableV2($listeLieuQuery, $_GET, false))
    ->setTitle("Liste des " . $listeLieuQuery->count("lieu.LIE_NUM", true) . " PDV")
    ->setClasses("tabBalance")
    ->setStyle([
        "LIE_CODE" => [
            "min-width" => "70px",
            "text-align" => "center"
        ],
        "LIE_NOM" => [
            "min-width" => "300px"
        ]
    ])
    ->sortable("LIE_CODE", "LIE_NOM")
    ->columns([
        "LIE_CODE" => "CODE",
        "LIE_NOM" => "PDV"
    ])
    ->format('LIE_NOM', function($value, $line) {
        return "<a href='../LieuBack/open.php?LIE_NUM=" . $line['LIE_NUM'] . "' style='text-decoration:none;font-weight: bolder;'>" . $value . "</a>";
    });

include_once __DIR__ . '/../../Templates/Lieu/ListeTemplate.php';

