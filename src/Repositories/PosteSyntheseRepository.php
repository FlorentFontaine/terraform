<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class PosteSyntheseRepository
{
    /**
     * R�cup�re un ou plusieurs postes synth�se
     *
     * @param ?int $codePosteSynthese
     * @return array
     */
    public function getPosteSynthese(?int $codePosteSynthese = null): array
    {
        return $this->getPosteSyntheseQuery($codePosteSynthese)->getAll();
    }

    /**
     * R�cup�re un ou plusieurs postes synth�se par type
     *
     * @param string $type
     * @return array
     */
    public function getPosteSyntheseByType(string $type): array
    {
        return $this->getPosteSyntheseQuery(null, $type)->getAll();
    }

    /**
     * Retourne la requ�te pour les postes de synth�se, prenant en comptes le type ou un id en param�tre
     *
     * @param int|null $codePosteSynthese
     * @param string|null $type
     * @return QueryBuilder
     */
    public function getPosteSyntheseQuery(?int $codePosteSynthese = null, ?string $type = null): QueryBuilder
    {
        $query = (new QueryBuilder())
            ->select('compteposte_synthese.*, SUM(comptes.CPT_VISIBLE) as visible')
            ->index('codePoste_synthese')
            ->from('compteposte_synthese')
            ->leftJoin('comptes', 'comptes.codePoste_synthese = compteposte_synthese.codePoste_synthese')
            ->groupBy('compteposte_synthese.codePoste_synthese')
            ->having('visible > 0')
            ->orderBy('compteposte_synthese.ordre');

        if ($codePosteSynthese) {
            $query->where('compteposte_synthese.codePoste_synthese = :codePoste_synthese')
                ->setParam('codePoste_synthese', $codePosteSynthese);
        }

        if ($type) {
            $query->where('compteposte_synthese.Type = :type')
                ->setParam('type', $type)
                ->where('compteposte_synthese.Famille != "ONFR"');
        }

        return $query;
    }
}
