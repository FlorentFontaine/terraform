<?php

/**
 * Class User
 */
class User
{
    public $Var;
    public $Infos;
    public $Aut;
    public $Type;
    public $Nom;
    public $Prenom;
    public $Niveau;
    public $Mail;

    public $NomTableUser;
    public $NomTableIdUser;
    public $NumTableIdUser;

    public function getAut($Page, $Element = null, $Type = "text")
    {
        if (
            isset($_SESSION["ioreport_maxMoisHisto"])
            && $_SESSION["MoisHisto"] < $_SESSION["ioreport_maxMoisHisto"]
            && ($_SESSION["station_STA_MAINTENANCE"] < date("Y-m-d H:i"))
        ) {
            $this->Aut["Rg"]["Rg"] = false;

            $this->Aut["Balance"]["Balance"] = false;

            $this->Aut["prev"]["prev"] = false;
            $this->Aut["prevprod"]["prevprod"] = false;
            $this->Aut["prevcharge"]["prevcharge"] = false;

            $this->Aut["Garde"]["Garde"] = false;

            $this->Aut["outil"]["outil"] = false;
        }

        if (!isset($this->Aut[$Page][$Page]) || !$this->Aut[$Page][$Page]) {
            return HTML::disabledElement($Type);
        }

        // droit sur la page, mais pas sur cette element
        if (
            (!isset($this->Aut[$Page][$Page]) || !$this->Aut[$Page][$Page])
            || (
                $Element && $this->Aut[$Page][$Page]
                && isset($this->Aut[$Page]["restri" . $Element]) && $this->Aut[$Page]["restri" . $Element]
            )
        ) {
            return HTML::disabledElement($Type);
        }

        return "";
    }
}
