<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class DetailPosteRepository
{
    public function getDetailCompteByPoste($codePoste, ?string $type = null): array
    {
        $queryCompteAchat = (new QueryBuilder())
            ->from("comptes")
            ->where("Type = 'achat'");

        if(is_array($codePoste)) {
            $queryCompteAchat->where("comptes.codePoste IN (" . implode(",", $codePoste) . ")");
        } else {
            $queryCompteAchat->where("comptes.codePoste = :codePoste")
                ->setParam("codePoste", $codePoste);
        }
        
        $monCompteAchat = $queryCompteAchat->get();

        $query = (new QueryBuilder())
            ->select("comptes.*, compteposte.SsFamille, compteposte.Libelle")
            ->from("comptes")
            ->index("comptes.code_compte")
            ->leftJoin("compteposte", "compteposte.codePoste = comptes.codePoste");

        if(is_array($codePoste)) {
            $query->where("comptes.codePoste IN (" . implode(",", $codePoste) . ")");
        } else {
            $query->where("comptes.codePoste = :codePoste")
            ->setParam("codePoste", $codePoste);
        }

        if ($type) {
            $query->where("comptes.Type = :type")
                ->setParam("type", $type);
        } elseif (isset($monCompteAchat["code_compte"]) && $monCompteAchat["code_compte"]) {
            $query
                ->orWhere("comptes.Stock = :codeCompte")
                ->orWhere("comptes.VarStock = :codeCompte")
                ->setParam("codeCompte", $monCompteAchat["code_compte"]);
        }

        return $query->getAll();
    }

    public function getDetailCompteByPosteBySlug($slug, ?string $type = null): array
    {

        $query = (new QueryBuilder())
            ->select("comptes.*, compteposte.SsFamille, compteposte.Libelle")
            ->from("comptes")
            ->index("comptes.code_compte")
            ->leftJoin("compteposte", "compteposte.codePoste = comptes.codePoste");

        if(is_array($slug)){
            $quotedArray = array_map(function($item) {
                return '"' . addslashes(utf8_decode($item)) . '"';
            }, $slug);
            $query->where("compteposte.SsFamille IN (" . implode(",", $quotedArray) . ")");
        } else {
            $query->where('compteposte.SsFamille = "' . addslashes(utf8_decode($slug)) . '"');
        }
        

        if ($type) {
            $query->where("comptes.Type = :type")
                ->setParam("type", $type);
        }

        return $query->getAll();
    }
}
