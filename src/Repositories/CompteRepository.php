<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class CompteRepository
{
    /**
     * Récupère un ou tous les comptes
     * @param ?int $codeCompte
     * @return array
     */
    public function getCompte(?int $codeCompte = null): array {
        $query = (new QueryBuilder())
            ->select('comptes.*')
            ->index('code_compte')
            ->from('comptes')
            ->where('comptes.CPT_VISIBLE = 1')
            ->orderBy('comptes.numero');

        if ($codeCompte) {
            $query = $query->where('comptes.codeCompte = :codeCompte')
                ->setParam('codeCompte', $codeCompte);
        }

        return $query->getAll();
    }

    public function getSyntheseProjectionComptesQuery(): QueryBuilder
    {
        return (new QueryBuilder())
            ->select('comptes.*')
            ->from('comptes')
            ->join("compteposte_synthese", "comptes.codeposte_synthese = compteposte_synthese.codePoste_synthese")
            ->where('comptes.CPT_VISIBLE = 1')
            ->orderBy('comptes.numero');
    }
}
