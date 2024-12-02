<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class PosteRepository
{
    /**
     * récupère un ou tous les postes
     * @param $codePoste
     * @return array
     */
    public function getPoste($codePoste = null) {
        $query = (new QueryBuilder())
            ->select('compteposte.*, SUM(comptes.CPT_VISIBLE) as visible')
            ->index('codePoste')
            ->from('compteposte')
            ->leftJoin('comptes', 'comptes.codePoste = compteposte.codePoste')
            ->groupBy('compteposte.codePoste')
            ->having('visible > 0')
            ->orderBy('compteposte.ordre');

        if ($codePoste) {
            if(is_array($codePoste)) {
                $query->where('compteposte.codePoste IN (' . implode(",", $codePoste) . ')');
            } else {
                $query->where('compteposte.codePoste = :codePoste')
                ->setParam('codePoste', $codePoste);
            }

            return $query->get();
        }

        return $query->getAll();
    }

    /**
     * récupère un ou tous les postes par type
     * @param string $type
     * @return array
     */
    public function getPosteByType(string $type): array {
        $query = (new QueryBuilder())
            ->select('compteposte.*, SUM(comptes.CPT_VISIBLE) as visible')
            ->index('codePoste')
            ->from('compteposte')
            ->leftJoin('comptes', 'comptes.codePoste = compteposte.codePoste')
            ->where('compteposte.type = :type')
            ->setParam('type', $type)
            ->groupBy('compteposte.codePoste')
            ->having('visible > 0')
            ->orderBy('compteposte.ordre');

        return $query->getAll();
    }

    public function getPosteBySousFamille($famille): array {
        $query = (new QueryBuilder())
            ->select('compteposte.*, SUM(comptes.CPT_VISIBLE) as visible')
            ->index('codePoste')
            ->from('compteposte')
            ->leftJoin('comptes', 'comptes.codePoste = compteposte.codePoste');

        if(is_array($famille)){
            $quotedArray = array_map(function($item) {
                return "'" . addslashes($item) . "'";
            }, $famille);
            $query->where('compteposte.SsFamille IN (' . implode(",", $quotedArray) . ')');
        } else {
            $query->where('compteposte.SsFamille = :SsFamille')
            ->setParam('SsFamille', $famille);
        }

        $query->groupBy('compteposte.codePoste')
            ->having('visible > 0')
            ->orderBy('compteposte.ordre');

        return $query->getAll();
    }
}
