<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class LieuRepository
{
    /**
     * Retourne un lieu à partir de son id
     *
     * @param int $id
     * @return array
     */
    public function getLieuByID(int $id): array
    {
        return $this->getLieuQuery()
            ->where("lieu.LIE_NUM = :LIE_NUM")
            ->setParam("LIE_NUM", $id)
            ->get();
    }

    public function getLieux(): array
    {
        return $this->getLieuQuery()->getAll();
    }

    /**
     * Retourne la requête permettant de lister tous les lieux
     *
     * @return QueryBuilder
     */
    public function getLieuQuery(): QueryBuilder
    {
        $query = (new QueryBuilder())
            ->select("DISTINCT lieu.*")
            ->from("lieu");

        $this->getJoinByUser($query);
        $this->getWhereByUser($query);

        if (isset($_POST['CAB_NUM']) && $_POST['CAB_NUM'] && $_POST['CAB_NUM'] != -1) {
            $this->getJoinByUser($query, 'comptable');
            $query->where("cabinet.CAB_NUM = :CAB_NUM")
                ->setParam("CAB_NUM", $_POST['CAB_NUM']);
        }

        if (isset($_POST['codeChefSecteur']) && $_POST['codeChefSecteur'] && $_POST['codeChefSecteur'] != -1) {
            $query->where("lieu.codeChefSecteur = :CDS")
                ->setParam("CDS", $_POST['codeChefSecteur']);
        }

        $query->where("lieu.LIE_ACTIVE = :LIE_ACTIVE");
        if (isset($_POST['inactif']) && $_POST['inactif'] === "oui") {
            $query->setParam("LIE_ACTIVE", "non");
        } else {
            $query->setParam("LIE_ACTIVE", "oui");
        }

        return $query;
    }

    /**
     * Définie les clauses JOIN en fonction de l'utilisateur pour la requête qui liste tous les lieux
     *
     * @param QueryBuilder $query
     * @param string $forceUserType
     * @return void
     */
    private function getJoinByUser(QueryBuilder $query, string $forceUserType = '')
    {
        $user = $_SESSION['User'];
        $userType = ($forceUserType === '') ? get_class($user) : $forceUserType;

        if ($userType === 'agip') {
            $query->leftJoin("station", "station.LIE_NUM = lieu.LIE_NUM");

            if (!in_array($user->Type, ["Secteur", "Region"])) {
                $query->leftJoin("stationcc", "station.STA_NUM = stationcc.STA_NUM")
                    ->leftJoin("comptable", "comptable.CC_NUM = stationcc.CC_NUM")
                    ->leftJoin("cabinet", "cabinet.CAB_NUM = comptable.CAB_NUM");
            } else {
                $query->join("chef" . $user->Type, "on chef" . $user->Type . ".codeChef" . $user->Type . " = lieu.codeChef" . $user->Type);
            }
        } elseif ($userType === 'comptable') {
            // Si on est ADMIN ou siège, on ne rajoute pas cette condition, car elle est déjà appliquée juste en haut
            if (in_array($user->Type, ["Secteur", "Region"])) {
                $query->leftJoin("station", "station.LIE_NUM = lieu.LIE_NUM")
                    ->leftJoin("stationcc", "station.STA_NUM = stationcc.STA_NUM")
                    ->leftJoin("comptable", "comptable.CC_NUM = stationcc.CC_NUM")
                    ->leftJoin("cabinet", "cabinet.CAB_NUM = comptable.CAB_NUM");
            }
        }
    }

    /**
     * Définie les clauses WHERE en fonction de l'utilisateur pour la requête qui liste tous les lieux
     *
     * @param QueryBuilder $query
     * @return void
     */
    private function getWhereByUser(QueryBuilder $query)
    {
        $user = $_SESSION['User'];
        $userType = get_class($user);

        if ($userType == 'agip' && in_array($user->Type, ["Secteur", "Region"])) {
            $query->where("lieu.codeChef" . $user->Type . " = :USER")
                ->setParam("USER", $user->Var["codeChef" . $user->Type]);
        }
    }
}
