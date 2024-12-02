<?php


// On fait la requ�te pour avoir nos DATA
use htmlClasses\TableV2;

$query = new QueryBuilder();

// Cr�ation du tableau de rendu
$table = (new TableV2($query, $_GET))
    // On sp�cifie les colonnes du tableau dans l'ordre d'affichage
    ->columns([
        'id' => 'ID',
        'name' => 'Nom',
        'city' => 'Ville',
        'price' => 'Prix',
    ])
    // On d�finit les champs qui sont cliquables pour trier sur la colonne
    ->sortable('id', 'city', 'price')
    // On sp�cifie qu'il faut utiliser ce formatage pour cette colonne
    ->format('price', function ($value, $line) {
        // On effectue une manipulation sur la donn�e
        // la ligne <TR> est �galement accessible dans le formateur
    });

// Affichage du tableau
$table->render();
