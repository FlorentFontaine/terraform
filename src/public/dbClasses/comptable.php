<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../dbClasses/User.php";
require_once __DIR__ . "/../htmlClasses/html.php";

class comptable extends User
{
    public $Var;
    public $Infos;
    public $Mail;

    function __construct($prop)
    {
        foreach ($prop as $key => $value) {
            $this->Var[$key] = $value;
        }

        $this->Nom = $prop["CC_NOM"];
        $this->initInfos();
        $this->setAut();//init des autorisations des pages

        $this->NomTableUser = "comptable";
        $this->NomTableIdUser = "CC_NUM";
        $this->NumTableIdUser = $this->Var["CC_NUM"];

        $this->Niveau = 2;
    }

    private function initInfos()
    {
        $tab["Type"] = "comptable";
        $tab["Name"] = $this->Var["CC_NOM"];
        $tab["mail"] = $this->Var["CC_MAIL"];
        $tab["mdp"] = $this->Var["CC_MDP"];

        $this->Infos = $tab;
        $this->Type = "comptable";
        $this->Mail = $this->Var["CC_MAIL"];
    }

    private function setAut()
    {
        $this->Aut["Rg"]["Rg"] = true;
        $this->Aut["Rg"]["StockInit"] = true;

        $this->Aut["Balance"]["Balance"] = true;

        $this->Aut["prev"]["prev"] = true;

        if (!isset($_SESSION["station_STA_TYPE_SHELL"]) || !$_SESSION["station_STA_TYPE_SHELL"]) {
            $this->Aut["prevprod"]["prevprod"] = true;
            $this->Aut["prevcharge"]["prevcharge"] = true;
        }

        $this->Aut["FormStation"]["FormStation"] = true;

        $this->Aut["option"]["option"] = true;

        $this->Aut["imp"]["imp"] = true;

        $this->Aut["station"]["station"] = true;

        $this->Aut["Garde"]["Garde"] = true;

        $this->Aut["lieu"]["lieu"] = true;
        $this->Aut["lieu"]["create"] = true;

        $this->Aut["outil"]["outil"] = true;

        $this->Aut["impression"]["impression"] = true;

    }

    function WhereRequired($table)
    {
        $where = "";
        switch ($table) {
            case "comptable":
                if (!$this->Var["CC_IS_ADMIN"]) {
                    $where = " and comptable.CC_NUM = '" . $this->Var["CC_NUM"] . "'";
                } elseif ($this->Var["CC_IS_ADMIN"]) {
                    $where = " and comptable.CAB_NUM = '" . $this->Var["CAB_NUM"] . "'";
                }
                break;

            case "station":
                if (!$this->Var["CC_IS_ADMIN"]) {
                    $where = " and comptable.CC_NUM = '" . $this->Var["CC_NUM"] . "'";
                } elseif ($this->Var["CC_IS_ADMIN"]) {
                    $where = " and cabinet.CAB_NUM = '" . $this->Var["CAB_NUM"] . "'";
                }
                break;

            case "lieu":
            default:
                break;
        }

        return $where;
    }

    function JoinRequired($table)
    {
        $join = "";
        switch ($table) {
            case "station":
                $join = " join stationcc on station.STA_NUM = stationcc.STA_NUM
                        join comptable on comptable.CC_NUM = stationcc.CC_NUM
                        join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM";
                break;

            case "cabinet":
                break;
            case "comptable":
                $join = " join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM ";
                break;

            case "lieu":
            default:
                break;
        }

        return $join;
    }
}
