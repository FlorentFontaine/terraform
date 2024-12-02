<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../dbClasses/User.php";
require_once __DIR__ . "/../htmlClasses/html.php";

class agip extends User
{
    function __construct($prop, $Type = null)
    {
        foreach ($prop as $key => $value) {
            $this->Var[$key] = $value;
            $_SESSION["agip_$key"] = $value;
        }

        $_SESSION["agip_AG_NUM"] = 1;

        $this->Nom = $prop["Nom"];
        $this->Prenom = $prop["Prenom"];
        $this->Type = $Type;// pour savoir si region, secteur, ou agip dans WhereRequired et JoinRequired

        if ($Type != "agip") {
            if ($Type != "Vente") {
                $this->Niveau = 3;
            } else {
                $this->Niveau = 4;
            }

            $this->NomTableUser = "chef" . $Type;
            $this->NomTableIdUser = "codeChef" . $Type;
            $this->NumTableIdUser = $this->Var["codeChef" . $Type];
        } else {
            $this->Niveau = 4;
            $this->NomTableUser = "agip";
            $this->NomTableIdUser = "AG_NUM";
            $this->NumTableIdUser = $this->Var["AG_NUM"];
        }

        $this->initInfos();
        $this->setAut();//init des autorisations des pages
    }

    private function initInfos()
    {
        $tab["Type"] = "agip";
        $tab["Name"] = $this->Var["Nom"] . " " . $this->Var["Prenom"];

        if ($this->Var["AG_MAIL"]) {
            $tab["mail"] = $this->Var["AG_MAIL"];
            $tab["mdp"] = $this->Var["AG_MDP"];
        } else {
            $tab["mail"] = $this->Var["E_Mail"];
            $tab["mdp"] = $this->Var["Mot_de_passe"];
        }

        $this->Infos = $tab;
        $this->Mail = $tab["mail"];
    }

    private function setAut()
    {
        $this->Aut["station"]["station"] = true;

        if ($this->Var["AG_TYPE"] == "VISU") {
            $this->Aut["station"]["restricreate"] = true;
            $this->Aut["lieu"]["restricreate"] = true;
            $this->Aut["fichiersDepot"]["fichiersDepot"] = true;
            $this->Aut["fichiersDepot"]["restridepotform"] = true;
            $this->Aut["fichiersDepot"]["restrifichiersSup"] = true;
        } else {
            $this->Aut["FormStation"]["FormStation"] = true;
            $this->Aut["BENCHMARK"]["BENCHMARK"] = true;
            $this->Aut["CRPNew"]["CRPNew"] = true;
            $this->Aut["fichiersDepot"]["fichiersDepot"] = true;
            $this->Aut["CondPart"]["CondPart"] = true;
            $this->Aut["CRPModifCle"]["CRPModifCle"] = true;
        }

        $this->Aut["benchmark"]["benchmark"] = true;

        if ($this->Type == "Secteur" || $this->Type == "Region") {
            $this->Aut["lieu"]["restricreate"] = true;
            $this->Aut["station"]["restricreate"] = true;
            $this->Aut["FormStation"]["FormStation"] = false;
            $this->Aut["fichiersDepot"]["restrifichiersSup"] = true;
        }

        $this->Aut["lieu"]["lieu"] = true;
        $this->Aut["inlieu"]["inlieu"] = true;

        $this->Aut["option"]["option"] = true;

        $this->Aut["impression"]["impression"] = true;
        $this->Aut["impression"]["restri1"] = true;
    }

    function WhereRequired($table)
    {
        $where = "";
        switch ($table) {
            case "lieu":
            case "station":
                if ($this->Type == "Secteur" || $this->Type == "Region") {
                    $where = " and lieu.codeChef" . $this->Type . " = " . $this->Var["codeChef" . $this->Type];
                }
                break;

            case "cabinet":
                $where = "  and cabinet.CAB_NOM not like '%NR%' ";
                break;

            default:
                break;
        }

        return $where;
    }

    function JoinRequired($table)
    {
        $join = "";
        switch ($table) {
            case "lieu":
                if ($this->Type != "Secteur" && $this->Type != "Region") {
                    $join = " left join station on station.LIE_NUM = lieu.LIE_NUM
                            left join stationcc on station.STA_NUM = stationcc.STA_NUM
                            left join comptable on comptable.CC_NUM = stationcc.CC_NUM
                            left join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM";
                    break;
                }

                $join = " left join station on station.LIE_NUM = lieu.LIE_NUM
                            join chef" . $this->Type . " on chef" . $this->Type . ".codeChef" . $this->Type . " = lieu.codeChef" . $this->Type;
                break;

            case "station":
                $join = " join lieu on lieu.LIE_NUM = station.LIE_NUM
                        left join stationcc on station.STA_NUM = stationcc.STA_NUM
                        left join comptable on comptable.CC_NUM = stationcc.CC_NUM
                        left join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM";
                break;

            case "comptable":
                $join = " join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM ";
                break;

            default:
                break;
        }
        return $join;
    }

}
