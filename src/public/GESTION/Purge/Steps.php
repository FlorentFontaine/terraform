<?php

// Définition des étapes de la réinitialisation du bac à sable.
// ATTENTION ! L'élément KEY doit correspondre au nom du fichier .SQL.

return [
    [
        'KEY' => 'purge',
        'DESCRIPTION' => 'Purge de la base de données'
    ],
    [
        'KEY' => 'set_pdv',
        'DESCRIPTION' => 'Initialisation des PDV'
    ],
    [
        'KEY' => 'set_sarl',
        'DESCRIPTION' => 'Initialisation des SARL'
    ],
    [
        'KEY' => 'set_dossier',
        'DESCRIPTION' => 'Initialisation des exercices comptables'
    ],
    [
        'KEY' => 'set_siege',
        'DESCRIPTION' => 'Initialisation des accès CDS et CDR'
    ],
    [
        'KEY' => 'set_comptable',
        'DESCRIPTION' => 'Initialisation des accès comptable'
    ],
    [
        'KEY' => 'set_station_comptable',
        'DESCRIPTION' => 'Attribution des comptables sur les SARL'
    ],
    [
        'KEY' => 'set_gerant',
        'DESCRIPTION' => 'Initialisation des accès gérant'
    ],
    [
        'KEY' => 'set_crp',
        'DESCRIPTION' => 'Définition des CRP'
    ],
    [
        'KEY' => 'set_crp_values',
        'DESCRIPTION' => 'Insertion des données des CRP'
    ],

    // Exercice N-1

    [
        'KEY' => 'set_balanceimport_N1',
        'DESCRIPTION' => 'Définition des imports de balance N-1'
    ],
    [
        'KEY' => 'set_balance_N1',
        'DESCRIPTION' => 'Insertion des données de balance N-1'
    ],
    [
        'KEY' => 'set_resultat_poste_N1',
        'DESCRIPTION' => 'Insertion des valorisations des postes au prévisionnel N-1'
    ],
    [
        'KEY' => 'set_calcul_marge_N1',
        'DESCRIPTION' => 'Définition stocks et taux de marge N-1'
    ],
    [
        'KEY' => 'set_rg_N1',
        'DESCRIPTION' => 'Insertion des RG N-1'
    ],
    [
        'KEY' => 'set_saison_N1',
        'DESCRIPTION' => 'Définition de la saisonnalité N-1'
    ],
    [
        'KEY' => 'set_carburant_N1',
        'DESCRIPTION' => 'Définition des volumes carburant N-1'
    ],
    [
        'KEY' => 'set_bench_N1',
        'DESCRIPTION' => 'Définition des données du benchmark N-1'
    ],

    // Exercice N

    [
        'KEY' => 'set_balanceimport_N',
        'DESCRIPTION' => 'Définition des imports de balance N'
    ],
    [
        'KEY' => 'set_balance_N',
        'DESCRIPTION' => 'Insertion des données de balance N'
    ],
    [
        'KEY' => 'set_resultat_poste_N',
        'DESCRIPTION' => 'Insertion des valorisations des postes au prévisionnel N'
    ],
    [
        'KEY' => 'set_calcul_marge_N',
        'DESCRIPTION' => 'Définition stocks et taux de marge N'
    ],
    [
        'KEY' => 'set_rg_N',
        'DESCRIPTION' => 'Insertion des RG N'
    ],
    [
        'KEY' => 'set_saison_N',
        'DESCRIPTION' => 'Définition de la saisonnalité N'
    ],
    [
        'KEY' => 'set_carburant_N',
        'DESCRIPTION' => 'Définition des volumes carburant N'
    ],
    [
        'KEY' => 'set_bench_N',
        'DESCRIPTION' => 'Définition des données du benchmark N'
    ],
];
