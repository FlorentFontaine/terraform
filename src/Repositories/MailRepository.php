<?php

namespace Repositories;

use Classes\DB\QueryBuilder;

class MailRepository
{
    public function getMailFormatted($LIE_NUM, $DOS_NUM, string $type): string
    {
        $mails = $this->getAllMail($LIE_NUM, $DOS_NUM)[$type];

        return implode(";", $mails);
    }
    public function getAllMail($LIE_NUM, $DOS_NUM): array
    {
        return [
            "mailsta" => $this->getMailStation($LIE_NUM, $DOS_NUM),
            "mailcds" => $this->getMailChefSecteur($LIE_NUM, $DOS_NUM),
            "mailcdr" => $this->getMailChefRegion($LIE_NUM, $DOS_NUM),
            "mailcdv" => $this->getMailChefVente($LIE_NUM, $DOS_NUM),
            "mailcc" => $this->getMailComptable($LIE_NUM, $DOS_NUM),
        ];
    }

    public function getMailStation(int $lieNum, int $dosNum = null): array
    {
        $query = (new QueryBuilder())
            ->select("station.STA_MAIL as mailsta")
            ->from("station")
            ->join("lieu", "lieu.LIE_NUM = station.LIE_NUM");

            $this->getWhereClauseOnLieNum($query, $lieNum);

        if ($dosNum) {
            $this->getJoinByType($query, "station", $dosNum);
        }
        
        return $query->getAll();
    }

    public function getMailChefSecteur(int $lieNum, int $dosNum = null): array
    {
        $query = (new QueryBuilder())
            ->select("chefSecteur.E_Mail as mailcds")
            ->from("chefSecteur")
            ->join("lieu", "lieu.codeChefSecteur = chefSecteur.codeChefSecteur");

            $this->getWhereClauseOnLieNum($query, $lieNum);

        if ($dosNum) {
            $this->getJoinByType($query, "CDS", $dosNum);
        }
        
        return $query->getAll();
    }

    public function getMailChefRegion(int $lieNum, int $dosNum = null): array
    {
        $query = (new QueryBuilder())
            ->select("chefRegion.E_Mail as mailcdr")
            ->from("chefRegion")
            ->join("lieu", "lieu.codeChefRegion = chefRegion.codeChefRegion");

            $this->getWhereClauseOnLieNum($query, $lieNum);

        if ($dosNum) {
            $this->getJoinByType($query, "CDR", $dosNum);
        }
        
        return $query->getAll();
    }

    public function getMailChefVente(int $lieNum, int $dosNum = null): array
    {
        $query = (new QueryBuilder())
            ->select("chefVente.E_Mail as mailcdv")
            ->from("chefVente")
            ->join("lieu", "lieu.codeChefVente = chefVente.codeChefVente");

            $this->getWhereClauseOnLieNum($query, $lieNum);

        if ($dosNum) {
            $this->getJoinByType($query, "CDV", $dosNum);
        }
        
        return $query->getAll();
    }

    public function getMailComptable(int $lieNum, int $dosNum = null): array
    {
        $query = (new QueryBuilder())
            ->select("comptable.CC_MAIL as mailcc")
            ->from("comptable")
            ->join("stationcc", "stationcc.CC_NUM = comptable.CC_NUM")
            ->join("station", "station.STA_NUM = stationcc.STA_NUM")
            ->join("lieu", "lieu.LIE_NUM = station.LIE_NUM");

            $this->getWhereClauseOnLieNum($query, $lieNum);

            if ($dosNum) {
                $this->getJoinByType($query, "comptable", $dosNum);
            }
        
        return $query->getAll();
    }
    
    private function getJoinByType(QueryBuilder $query, string $type, $dosNum)
    {
        $query->join("dossier", "dossier.STA_NUM = station.STA_NUM")
            ->where("dossier.DOS_NUM = :DOS_NUM")
            ->setParam("DOS_NUM", $dosNum);

        if (in_array($type, ["CDV", "CDR", "CDS"])) {
            $query->join("station", "lieu.LIE_NUM = station.LIE_NUM");
        }
    }

    private function getWhereClauseOnLieNum(QueryBuilder $query, int $lieNum)
    {
        $query
            ->where("lieu.LIE_NUM = :LIE_NUM")
            ->setParam("LIE_NUM", $lieNum);
    }
}
