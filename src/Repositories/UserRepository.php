<?php

namespace Repositories;

require_once __DIR__ . '/../../Classes/DB/QueryBuilder.php';

use QueryBuilder;

class UserRepository
{
    /**
     * Récupère les chefs de région
     * @return array
     */
    public function getChefRegion() {

        return (new QueryBuilder())
            ->select("chefRegion.*")
            ->index("codechefRegion")
            ->from("chefRegion")
            ->join("lieu", "lieu.codechefRegion = chefRegion.codechefRegion")
            ->where("lieu.LIE_NUM = :LIE_NUM")
            ->setParam("LIE_NUM", $_SESSION["station_LIE_NUM"])
            ->orderBy("chefRegion.Nom,chefRegion.Prenom")
            ->getAll();
    }

    /**
     * Récupère les chefs de secteur
     * @return array
     */
    public function getChefSecteur() {
        return (new QueryBuilder())
            ->select("chefSecteur.*")
            ->index("codechefSecteur")
            ->from("chefSecteur")
            ->join("lieu", "lieu.codechefSecteur = chefSecteur.codechefSecteur")
            ->where("lieu.LIE_NUM = :LIE_NUM")
            ->setParam("LIE_NUM", $_SESSION["station_LIE_NUM"])
            ->orderBy("chefSecteur.Nom,chefSecteur.Prenom")
            ->getAll();
    }
}
