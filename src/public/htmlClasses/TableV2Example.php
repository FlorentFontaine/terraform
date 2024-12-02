<?php


// On fait la requête pour avoir nos DATA
use htmlClasses\TableV2;

$query = new QueryBuilder();

// Création du tableau de rendu
$table = (new TableV2($query, $_GET))
    // On spécifie les colonnes du tableau dans l'ordre d'affichage
    ->columns([
        'id' => 'ID',
        'name' => 'Nom',
        'city' => 'Ville',
        'price' => 'Prix',
    ])
    // On définit les champs qui sont cliquables pour trier sur la colonne
    ->sortable('id', 'city', 'price')
    // On spécifie qu'il faut utiliser ce formatage pour cette colonne
    ->format('price', function ($value, $line) {
        // On effectue une manipulation sur la donnée
        // la ligne <TR> est également accessible dans le formateur
    });

// Affichage du tableau
$table->render();
