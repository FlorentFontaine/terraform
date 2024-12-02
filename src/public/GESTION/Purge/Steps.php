<?php

// D�finition des �tapes de la r�initialisation du bac � sable.
// ATTENTION ! L'�l�ment KEY doit correspondre au nom du fichier .SQL.

return [
    [
        'KEY' => 'purge',
        'DESCRIPTION' => 'Purge de la base de donn�es'
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
        'DESCRIPTION' => 'Initialisation des acc�s CDS et CDR'
    ],
    [
        'KEY' => 'set_comptable',
        'DESCRIPTION' => 'Initialisation des acc�s comptable'
    ],
    [
        'KEY' => 'set_station_comptable',
        'DESCRIPTION' => 'Attribution des comptables sur les SARL'
    ],
    [
        'KEY' => 'set_gerant',
        'DESCRIPTION' => 'Initialisation des acc�s g�rant'
    ],
    [
        'KEY' => 'set_crp',
        'DESCRIPTION' => 'D�finition des CRP'
    ],
    [
        'KEY' => 'set_crp_values',
        'DESCRIPTION' => 'Insertion des donn�es des CRP'
    ],

    // Exercice N-1

    [
        'KEY' => 'set_balanceimport_N1',
        'DESCRIPTION' => 'D�finition des imports de balance N-1'
    ],
    [
        'KEY' => 'set_balance_N1',
        'DESCRIPTION' => 'Insertion des donn�es de balance N-1'
    ],
    [
        'KEY' => 'set_resultat_poste_N1',
        'DESCRIPTION' => 'Insertion des valorisations des postes au pr�visionnel N-1'
    ],
    [
        'KEY' => 'set_calcul_marge_N1',
        'DESCRIPTION' => 'D�finition stocks et taux de marge N-1'
    ],
    [
        'KEY' => 'set_rg_N1',
        'DESCRIPTION' => 'Insertion des RG N-1'
    ],
    [
        'KEY' => 'set_saison_N1',
        'DESCRIPTION' => 'D�finition de la saisonnalit� N-1'
    ],
    [
        'KEY' => 'set_carburant_N1',
        'DESCRIPTION' => 'D�finition des volumes carburant N-1'
    ],
    [
        'KEY' => 'set_bench_N1',
        'DESCRIPTION' => 'D�finition des donn�es du benchmark N-1'
    ],

    // Exercice N

    [
        'KEY' => 'set_balanceimport_N',
        'DESCRIPTION' => 'D�finition des imports de balance N'
    ],
    [
        'KEY' => 'set_balance_N',
        'DESCRIPTION' => 'Insertion des donn�es de balance N'
    ],
    [
        'KEY' => 'set_resultat_poste_N',
        'DESCRIPTION' => 'Insertion des valorisations des postes au pr�visionnel N'
    ],
    [
        'KEY' => 'set_calcul_marge_N',
        'DESCRIPTION' => 'D�finition stocks et taux de marge N'
    ],
    [
        'KEY' => 'set_rg_N',
        'DESCRIPTION' => 'Insertion des RG N'
    ],
    [
        'KEY' => 'set_saison_N',
        'DESCRIPTION' => 'D�finition de la saisonnalit� N'
    ],
    [
        'KEY' => 'set_carburant_N',
        'DESCRIPTION' => 'D�finition des volumes carburant N'
    ],
    [
        'KEY' => 'set_bench_N',
        'DESCRIPTION' => 'D�finition des donn�es du benchmark N'
    ],
];
