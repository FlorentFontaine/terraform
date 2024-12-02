<?php

require_once __DIR__."/../auth/classes/User.class.php";
require_once __DIR__."/../dbClasses/AccesDonnees.php";
require_once __DIR__ . '/../../Init/bootstrap.php';

session_start();

use Classes\DB\Database;

class Utilisateurs {

    static $limit = 20;
    static $regexMail = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';

    static $transco = array(
        "CDR" => array(
            "table" => "chefregion",
            "colonnes" => array(
                "Nom"      => "USER_NOM",
                "Prenom"   => "USER_PRENOM",
                "E_Mail"   => "USER_MAIL",
                "Tel"      => "TELEPHONE",
                "Portable" => "PORTABLE",
                "Fax"      => "FAX"
            ),
            "ROLE_NUM" => "codeChefRegion"
        ),
        "CDS" => array(
            "table" => "chefsecteur",
            "colonnes" => array(
                "Nom"      => "USER_NOM",
                "Prenom"   => "USER_PRENOM",
                "E_Mail"   => "USER_MAIL",
                "Tel"      => "TELEPHONE",
                "Portable" => "PORTABLE",
                "Fax"      => "FAX"
            ),
            "ROLE_NUM" => "codeChefSecteur"
        ),
        "CDV" => array(
            "table" => "chefvente",
            "colonnes" => array(
                "Nom"      => "USER_NOM",
                "Prenom"   => "USER_PRENOM",
                "E_Mail"   => "USER_MAIL",
                "Tel"      => "TELEPHONE",
                "Portable" => "PORTABLE",
                "Fax"      => "FAX"
            ),
            "ROLE_NUM" => "codeChefVente"
        ),
        "SIEGE" => array(
            "table" => "agip",
            "colonnes" => array(
                "Nom"                             => "USER_NOM",
                "Prenom"                          => "USER_PRENOM",
                "AG_MAIL"                         => "USER_MAIL"
            ),
            "ROLE_NUM" => "AG_NUM"
        ),
        "COMPTABLE" => array(
            "table" => "comptable",
            "colonnes" => array(
                "CC_NOM"      => "USER_NOMPRENOM",
                "CAB_NUM"     => "CAB_NUM",
                "CC_TEL"      => "TELEPHONE",
                "CC_MAIL"     => "USER_MAIL",
                "CC_PORT"     => "PORTABLE",
                "CC_IS_ADMIN" => "CC_IS_ADMIN"
            ),
            "ROLE_NUM" => "CC_NUM"
        ),
        "STATION" => array(
            "table" => "gerant",
            "colonnes" => array(
                "STA_NUM"    => "STA_NUM",
                "GER_PRENOM" => "USER_PRENOM",
                "GER_NOM"    => "USER_NOM",
                "GER_MAIL"   => "USER_MAIL",
                "GER_NUMBER" => "GER_NUMBER"
            ),
            "ROLE_NUM" => "GER_NUM"
        )
    );

    static $transcoRole = array(
        "STATION"   => "sta",
        "SIEGE"     => "sie",
        "CDR"       => "cdr",
        "CDS"       => "cds",
        "CDV"       => "cdv",
        "COMPTABLE" => "cc"
    );

    /**
     * Fonction retournant la liste des utilisateurs suivants des filtres
     *
     * @param $d array - Tableau contenant la liste des filtres
     * @return $return array - Tableau ["data", "results"]
     */
    static function getUsers($d)
    {
        $return = array();

        $page = $d["page"];
        $offset = ($page - 1) * self::$limit;

        // Tri des données
        $order = "";
        switch($d["ORDER"])
        {
            case "USER_NOM_ASC":
                $order = " ORDER BY USER_NOM ASC";
                break;

            case "USER_NOM_DESC":
                $order = " ORDER BY USER_NOM DESC";
                break;

            case "USER_PRENOM_ASC":
                $order = " ORDER BY USER_PRENOM ASC";
                break;

            case "USER_PRENOM_DESC":
                $order = " ORDER BY USER_PRENOM DESC";
                break;

            case "ROLE_TYPE_ASC":
                $order = " ORDER BY ROLE_TYPE ASC";
                break;

            case "ROLE_TYPE_DESC":
                $order = " ORDER BY ROLE_TYPE DESC";
                break;

            default:
                break;
        }

        if($d["ROLE_TYPE"] == "COMPTABLE" && $d["ORDER"] == "CAB_NOM_ASC")
            $order = " ORDER BY CAB_NOM ASC, USER_NOM ASC";
        elseif($d["ROLE_TYPE"] == "COMPTABLE" && $d["ORDER"] == "CAB_NOM_DESC")
            $order = " ORDER BY CAB_NOM DESC, USER_NOM ASC";

        $where = "WHERE 1";

        if($d["ROLE_TYPE"])
            $where .= " AND ROLE_TYPE = '{$d["ROLE_TYPE"]}'";
        else
            $where .= " AND ROLE_TYPE NOT IN ('STATION')";

        if(trim($d["USER"]) != "")
            $where .= " AND (USER_NOM LIKE \"%".utf8_decode($d["USER"])."%\" OR USER_PRENOM LIKE \"%".utf8_decode($d["USER"])."%\")";

        $sql = "
            SELECT user.*, ROLE_TYPE, ROLE_NUM, CAB_NOM, CC_IS_ADMIN
            FROM user
            LEFT JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            LEFT JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'COMPTABLE'
            LEFT JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
            $where
            $order
            LIMIT ".self::$limit."
            OFFSET $offset
        ";

        $res = Database::query($sql);

        while($ligne = Database::fetchArray($res))
        {
            foreach($ligne as &$champ)
            {
                $champ = utf8_encode($champ);
            }

            $datas[] = $ligne;
        }

        // Récupération du nombre de page et du résultats
        $sqlResults = "
            SELECT count(DISTINCT USER_LOCKERS_ID) AS NB
            FROM user
            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            LEFT JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'COMPTABLE'
            LEFT JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
            $where
        ";
        $resResults = Database::query($sqlResults);
        $lnResults = Database::fetchArray($resResults);
        $nbResults = $lnResults["NB"];
        $nbPage = ceil($nbResults / self::$limit);

        $return["results"] = $nbResults;
        $return["data"] = $datas;
        $return["page"] = $nbPage;

        return $return;
    }

    /**
     * Fonction de génération du formulaire d'ajout / modification d'utilisateur
     *
     * @param $d array - Infos user si modification
     * @return $html string - Flux html du formulaire
     */
    static function getForm($d)
    {
        $selectedCOMPTABLE = $selectedCDS = $selectedCDV = $selectedCDR = $selectedTOTAL = "";
        $checkedAdminCC = "";
        $checkedSC = $checkedSA = $checkedSAL = $checkedEPP = $checkedEDE = $checkedHC = $checkedEC = $checkedRC = "";
        $disabledField = "";

        if($d["NOT_ADMIN"] && $d["ROLE_TYPE"] && $d["type"] == "add")
        {
            switch($d["ROLE_TYPE"])
            {
                case "COMPTABLE":
                    $selectedCOMPTABLE = "selected='selected'";
                    $ln["CAB_NUM"] = $_SESSION["logedVar"]["CAB_NUM"];
                    break;

                case "STATION":
                    $selectedSTATION = "selected='selected'";
                    $ln["STA_NUM"] = $d["STA_NUM"];
                    break;

                default:
                    break;
            }
        }

        if($d["type"] == "update")
        {
            $disabledField = "disabled='disabled'";
            // On récupère les infos de l'utilisateur
            switch($d["ROLE_TYPE"])
            {
                case "COMPTABLE":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL,
                        CC_TEL AS TELEPHONE, CC_PORT AS PORTABLE, CC_IS_ADMIN, CAB_NUM, ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'COMPTABLE'
                        JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedCOMPTABLE = "selected='selected'";
                    $ln["CC_IS_ADMIN"] == 1 ? $checkedAdminCC = "checked='checked'" : "";
                    break;

                case "STATION":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL, STA_NUM, ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'STATION'
                        JOIN gerant ON gerant.GER_NUM = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedSTATION = "selected='selected'";
                    break;

                case "CDR":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL,
                        Tel AS TELEPHONE, Portable AS PORTABLE, Fax AS FAX, ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'CDR'
                        JOIN chefregion ON chefregion.codeChefRegion = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedCDR = "selected='selected'";
                    break;

                case "CDS":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL,
                        Tel AS TELEPHONE, Portable AS PORTABLE, Fax AS FAX, ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'CDS'
                        JOIN chefsecteur ON chefsecteur.codeChefSecteur = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedCDS = "selected='selected'";
                    break;

                case "CDV":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL,
                        Tel AS TELEPHONE, Portable AS PORTABLE, Fax AS FAX, ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'CDV'
                        JOIN chefvente ON chefvente.codeChefVente = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedCDV = "selected='selected'";
                    break;

                case "SIEGE":
                    $sql = "
                        SELECT user.USER_NUM, USER_NOM, USER_PRENOM, USER_LOCKERS_ID, USER_MAIL,
                        ROLE_NUM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'SIEGE'
                        JOIN agip ON agip.AG_NUM = userhasrole.ROLE_NUM
                        WHERE user.USER_NUM = ".$d["USER_NUM"]."
                    ";
                    $res = Database::query($sql);
                    $ln = Database::fetchArray($res);
                    $selectedTOTAL = "selected='selected'";
                    break;
            }

            if($ln["USER_LOCKERS_ID"] != "")
                $actionOnLockers = "update";
        }

        $html = "
        <div>";

        if($d["type"] == "add")
            $html .= "<p>Avant d'ajouter un utilisateur, vous devez vérifier que l'email n'est pas déj&agrave; utilisé.</p>";

        $html .= "
            <form id='formUser'>
                <input type='hidden' name='actionOnMyreport' id='actionOnMyreport' value='".$d["type"]."'>
                <input type='hidden' name='actionOnLockers' id='actionOnLockers' value='".$actionOnLockers."'>
                <input type='hidden' name='USER_LOCKERS_ID' id='USER_LOCKERS_ID' value='".$ln["USER_LOCKERS_ID"]."'>
                <input type='hidden' name='ROLE_NUM' id='ROLE_NUM' value='".$ln["ROLE_NUM"]."'>
                <input type='hidden' name='USER_NUM' id='USER_NUM' value='".$ln["USER_NUM"]."'>
                <input type='hidden' name='STA_NUM' id='STA_NUM' value='".$ln["STA_NUM"]."'>
                <div class='formDiv'>
                    <label class='formLabel'>Email (*) : </label>
                    <input class='formInputSelect' type='text' name='USER_MAIL' id='USER_MAIL' value='".$ln["USER_MAIL"]."' ".$disabledField.">
                    <img id='spinnerMail' src='../../images/spinner.gif' alt='loader' style='width: 1.1em; display: none;'>
                    <img id='checkMail' src='../../images/check.png' alt='ok' style='width: 1.1em; display: none;'>
                </div>";
        if($d["type"] == "add")
            $html .= "
                <div class='formDiv'>
                    <a href='#' id='verifMail'>Vérifier l'unicité du mail</a>
                </div>";

        $display = "";
        if($d["type"] == "update")
            $display = "style='display:none;'";
        $html .= "
                <div class='formDiv' ".$display.">
                    <label class='formLabel'>Confirmation (*) : </label>
                    <input class='formInputSelect' type='text' name='USER_MAIL_CONF' id='USER_MAIL_CONF' value='".$ln["USER_MAIL"]."' ".$disabledField.">
                    <img id='errorMail' src='../images/suppicon.png' style='width: 1.1em; display: none;'>
                </div>";

        $html .= '
            <div class="separator"></div>
            <div class="formDiv">
                <label class="formLabel">Nom (*) : </label>
                <input class="formInputSelect" type="text" name="USER_NOM" id="USER_NOM" value="'.$ln["USER_NOM"].'">
                <img id="errorNom" src="../images/suppicon.png" style="width: 1.1em; display: none;">
            </div>
            <div class="formDiv">
                <label class="formLabel">Prénom (*) : </label>
                <input class="formInputSelect" type="text" name="USER_PRENOM" id="USER_PRENOM" value="'.$ln["USER_PRENOM"].'">
                <img id="errorPrenom" src="../images/suppicon.png" style="width: 1.1em; display: none;">
            </div>
        ';

        $display = "";
        if($d["NOT_ADMIN"])
            $display = "style='display:none;'";

        if($d["ROLE_TYPE"] == "STATION")
            $html .= "<input type='hidden' name='GER_NUMBER' value='".$d["GER_NUMBER"]."'>";


        $html .= "
                <div class='formDiv' ".$display.">
                    <label class='formLabel'>Type (*) : </label>
                    <select class='formInputSelect' name='ROLE_TYPE' id='ROLE_TYPE' ".$disabledField.">
                        <option ".$selectedCDR." value='CDR'>CDR</option>
                        <option ".$selectedCDS." value='CDS'>CDS</option>
                        <option ".$selectedCDV." value='CDV'>CDV</option>
                        <option ".$selectedCOMPTABLE." value='COMPTABLE'>COMPTABLE</option>
                        <option ".$selectedTOTAL." value='SIEGE'>SIEGE</option>
                        <option ".$selectedSTATION." style='display: none;' value='STATION'>GERANT</option>
                    </select>
                </div>
        ";

        $html .= "
                <div class='separator'></div>
                <div class='formDiv fieldDiv fieldCDR fieldCDS fieldCDV fieldCPT'>
                    <label class='formLabel'>Téléphone : </label>
                    <input class='formInputSelect' type='text' name='TELEPHONE' id='TELEPHONE' value='".$ln["TELEPHONE"]."'>
                </div>
                <div class='formDiv fieldDiv fieldCDR fieldCDS fieldCDV fieldCPT'>
                    <label class='formLabel'>Portable : </label>
                    <input class='formInputSelect' type='text' name='PORTABLE' id='PORTABLE' value='".$ln["PORTABLE"]."'>
                </div>
                <div class='formDiv fieldDiv fieldCDR fieldCDS fieldCDV'>
                    <label class='formLabel'>Fax : </label>
                    <input class='formInputSelect' type='text' name='FAX' id='FAX' value='".$ln["FAX"]."'>
                </div>
                <div class='separator'></div>
        ";

        $class = "";
        if($d["NOT_ADMIN"])
            $class = "NOT_ADMIN";

        $html .= "
                <div class='formDiv fieldDiv fieldCPT ".$class."'>
                    <label class='formLabel'>Cabinet (*) : </label>
                    <select class='formInputSelect' name='CAB_NUM' id='CAB_NUM'>
                        ".self::getCabinetsOptions($ln["CAB_NUM"])."
                    </select>
                    <img id='errorCabinet' src='../images/suppicon.png' style='width: 1.1em; display: none;'>
                </div>
        ";

        $html .= "
                <div class='formDiv fieldDiv fieldCPT'>
                    <label class='formLabel'>Administrateur : </label>
                    <input type='checkbox' name='CC_IS_ADMIN' id='CC_IS_ADMIN' value='1' ".$checkedAdminCC.">
                </div>
            </form>
        </div>
        ";

        return array(
            "html" => utf8_encode($html),
            "canDelete" => $_SESSION["USER_NUM"] == $d["USER_NUM"] ? 0 : 1
        );
    }

    /**
     * Fonction qui va retourner la liste des cabinets comptables sous forme d'options
     *
     * @return $html string - Html <option>
     */
    static function getCabinetsOptions($CAB_NUM = null)
    {
        $html = "<option value=''></option>";
        $sql = "
            SELECT CAB_NOM, CAB_NUM
            FROM cabinet
            ORDER BY CAB_NOM ASC
        ";
        $res = Database::query($sql);
        while($ln = Database::fetchArray($res))
        {
            $selected = "";
            if($CAB_NUM == $ln["CAB_NUM"]) $selected = "selected='selected'";
            $html .= "<option value='".$ln["CAB_NUM"]."' ".$selected.">".$ln["CAB_NOM"]."</option>";
        }

        return $html;
    }

    /**
     * Fonction de vérification de l'unicité du mail
     *
     * @param $d array - Tableau contenant l'email à vérifier
     * @return $return array - Tableau contenant les infos de retour
     */
    static function checkUniqueEmail($d)
    {
        $actionsToDo = array();
        $User = new User();

        $userOnLockers = $User->getUserByUsername($d["username"]);

        if($userOnLockers["success"] && $userOnLockers["data"])
        {
            $actionsToDo["data"] = $userOnLockers["data"];
            $actionsToDo["data"]["onLockers"] = 1;
        }

        if($d["role"] == "STATION")
        {
            $sql = "
                SELECT * FROM user
                JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM AND ROLE_TYPE = 'STATION'
                JOIN gerant ON gerant.GER_NUM = userhasrole.ROLE_NUM
                WHERE USER_MAIL = '".$d["username"]."'
                AND STA_NUM = ".$d["STA_NUM"]."
            ";
            $res = Database::query($sql);
            $ln = Database::fetchArray($res);

            // Si adresse email déjà utilisé sur la station, pas possible
            if($ln)
            {
                $actionsToDo["data"]["onIo"] = 1;
                return $actionsToDo;
            }

            $sql = "
                SELECT * FROM user
                JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                LEFT JOIN gerant ON gerant.GER_NUM = userhasrole.ROLE_NUM AND ROLE_TYPE = 'STATION' AND STA_NUM <> ".$d["STA_NUM"]." 
                WHERE USER_MAIL = '".$d["username"]."'
            ";
            $res = Database::query($sql);
            $ln = Database::fetchArray($res);

            if($ln)
            {
                $actionsToDo["data"]["gerantUpdate"] = 1;
                $actionsToDo["data"]["USER_NUM"] = $ln["USER_NUM"];
            }
        }
        else
        {
            $sql = "SELECT * FROM user WHERE USER_MAIL = '".$d["username"]."'";
            $res = Database::query($sql);
            $ln = Database::fetchArray($res);

            if($ln)
            {
                $actionsToDo["data"]["userUpdate"] = 1;
                $actionsToDo["data"]["USER_NUM"] = $ln["USER_NUM"];
            }
        }

        return $actionsToDo;
    }

    /**
     * Fonction de création d'un utilisateur dans My Report
     *
     * @param $d array - Tableau contenant les informations de l'utilisateur
     * @return $return array - Les infos de l'utilisateur créé
     */
    static function saveUser($d)
    {
        $User = new User();

        try
        {
            $datas = $d["datas"];
            // On vérifie les infos envoyées
            self::checkInformations($datas);

            // On formate les informations
            $datas["USER_MAIL"] = trim($datas["USER_MAIL"]);
            $datas["USER_PRENOM"] = ucfirst(trim($datas["USER_PRENOM"]));
            $datas["USER_NOM"] = mb_strtoupper(trim($datas["USER_NOM"]));

            // On ajoute / update l'utilisateur sur Lockers
            $role = self::$transcoRole[$datas["ROLE_TYPE"]];

            $datasLockers = array(
                "email" => $datas["USER_MAIL"],
                "firstName" => $datas["USER_PRENOM"],
                "lastName" => $datas["USER_NOM"],
                "role" => $role
            );

            $res = NULL;
            if($datas["actionOnLockers"] == "add")
                $res = $User->create($datasLockers);
            elseif($datas["actionOnLockers"] == "update" && $datas["USER_LOCKERS_ID"])
                $res = $User->update($datas["USER_LOCKERS_ID"], $datasLockers);

            if(!$res["data"] || $res["success"] == false)
                throw new Exception("Impossible de créer ou mettre &agrave; jour l'utilisateur sur le serveur d'authentification");

            // On ajoute / modifie l'utilisateur dans My Report
            $sqlInfos = self::$transco[$datas["ROLE_TYPE"]];
            $datas["USER_PRENOM"] = utf8_decode($res["data"]["firstName"]);
            $datas["USER_NOM"] = utf8_decode($res["data"]["lastName"]);
            if($datas["ROLE_TYPE"] == "COMPTABLE")
                $datas["USER_NOMPRENOM"] = $datas["USER_NOM"]." ".$datas["USER_PRENOM"];

            if($datas["actionOnMyreport"] == "add")
            {
                // Table de son type
                $columns = $values = "(";
                $first = true;
                foreach($sqlInfos["colonnes"] as $ioColonne => $datasColonne)
                {
                    if(!$first)
                    {
                        $columns .= ", ";
                        $values .= ", ";
                    }
                    $columns .= $ioColonne;
                    $values .= "\"".$datas[$datasColonne]."\"";

                    $first = false;
                }

                $columns .= ")";
                $values .= ")";

                $sql = "
                    INSERT INTO ".$sqlInfos["table"]."
                    ".$columns."
                    VALUES
                    ".$values."
                ";

                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de créer l'utilisateur dans la table correspondante");

                $pkTable = Database::lastPK();

                // Table user
                $sql = "
                    INSERT INTO user
                    (USER_PRENOM, USER_NOM, USER_MAIL, USER_LOCKERS_ID)
                    VALUES
                    (\"".$datas["USER_PRENOM"]."\", \"".$datas["USER_NOM"]."\", \"".$datas["USER_MAIL"]."\", \"".$res["data"]["id"]."\")
                ";
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de créer l'utilisateur dans la table user");

                $pkUser = Database::lastPK();

                // Table userhasrole
                $sql = "
                    INSERT INTO userhasrole
                    (USER_NUM, ROLE_TYPE, ROLE_NUM)
                    VALUES
                    (\"".$pkUser."\", \"".$datas["ROLE_TYPE"]."\", \"".$pkTable."\")
                ";
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de créer la liaison user / table correspondante");

                return array(
                    "data" => array(
                        "information" => utf8_encode("L'utilisateur a bien été enregistré")
                    )
                );
            }
            elseif($datas["actionOnMyreport"] == "update")
            {
                // Table de son type
                $sql = "
                    UPDATE ".$sqlInfos["table"]."
                    SET
                ";

                $first = true;
                foreach($sqlInfos["colonnes"] as $ioColonne => $datasColonne)
                {
                    if(!$first)
                        $sql .= ", ";
                    $sql .= $ioColonne." = \"".$datas[$datasColonne]."\"";

                    $first = false;
                }
                $sql .= " WHERE ".$sqlInfos["ROLE_NUM"]." = ".$datas["ROLE_NUM"];
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de mettre &agrave; jour l'utilisateur dans la table correspondante");

                // Table user
                $sql = "
                    UPDATE user
                    SET USER_NOM = \"".$datas["USER_NOM"]."\", USER_PRENOM = \"".$datas["USER_PRENOM"]."\", USER_MAIL = \"".$datas["USER_MAIL"]."\"
                    WHERE USER_NUM = ".$datas["USER_NUM"]."
                ";
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de mettre &agrave; jour l'utilisateur dans la table user");

                // Table userhasrole
                $sql = "
                    UPDATE userhasrole
                    SET ROLE_TYPE = \"".$datas["ROLE_TYPE"]."\"
                    WHERE USER_NUM = ".$datas["USER_NUM"]." AND ROLE_NUM = ".$datas["ROLE_NUM"]."
                ";
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de mettre &agrave; jour la liaison user / table correspondante");

                return array(
                    "data" => array(
                        "information" => utf8_encode("L'utilisateur a bien été mis &agrave; jour")
                    )
                );
            }
            elseif($datas["actionOnMyreport"] == "addUpdate")
            {
                // Créer une ligne dans la table de correspondance
                $columns = $values = "(";
                $first = true;
                foreach($sqlInfos["colonnes"] as $ioColonne => $datasColonne)
                {
                    if(!$first)
                    {
                        $columns .= ", ";
                        $values .= ", ";
                    }
                    $columns .= $ioColonne;
                    $values .= "\"".$datas[$datasColonne]."\"";

                    $first = false;
                }

                $columns .= ")";
                $values .= ")";

                $sql = "
                    INSERT INTO ".$sqlInfos["table"]."
                    ".$columns."
                    VALUES
                    ".$values."
                ";

                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de créer l'utilisateur dans la table correspondante");

                $pkTable = Database::lastPK();

                // Créer une liaison dans la table userhasrole
                $sql = "
                    INSERT INTO userhasrole
                    (USER_NUM, ROLE_TYPE, ROLE_NUM)
                    VALUES
                    (\"".$datas["USER_NUM"]."\", \"".$datas["ROLE_TYPE"]."\", \"".$pkTable."\")
                ";
                $resSQL = Database::query($sql);
                if(!$resSQL)
                    throw new Exception("Impossible de créer la liaison user / table correspondante");

                return array(
                    "data" => array(
                        "information" => utf8_encode("L'utilisateur a bien été enregistré")
                    )
                );
            }
        }
        catch(Exception $e)
        {
            return array(
                "error" => array(
                    "message" => utf8_encode($e->getMessage()),
                    "code" => $e->getCode()
                )
            );
        }
    }

    /**
     * Fonction qui va vérifier tous les champs envoyés
     *
     * @param $d array - Tableau contenant les données
     * @return boolean - True si ok, false sinon avec une erreur
     */
    static function checkInformations($d)
    {
        if(!$d["USER_MAIL"] || trim($d["USER_MAIL"]) == "" || !preg_match(self::$regexMail, $d["USER_MAIL"]))
            throw new Exception("Le mail n'est pas rempli ou est mal formaté");

        if(!$d["USER_PRENOM"] || trim($d["USER_PRENOM"]) == "")
            throw new Exception("Le prénom n'est pas saisi");

        if(!$d["USER_NOM"] || trim($d["USER_NOM"]) == "")
            throw new Exception("Le nom n'est pas saisi");

        // On vérifie que quand on ajoute un CDR, CDS, CDV, SIEGE ou COMPTABLE sur même cabinet
        // L'email n'est pas déjà pris
        if($d["actionOnMyreport"] == "addUpdate" && $d["ROLE_TYPE"] != "STATION")
        {
            $sql = "
                SELECT COUNT(*) AS HASROLE FROM user
                JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                WHERE user.USER_NUM = '".$d["USER_NUM"]."'
            ";

            switch($d["ROLE_TYPE"])
            {
                case "SIEGE":
                    $sql .= " AND ROLE_TYPE = 'SIEGE'";
                break;
    
                case "CDR":
                    $sql .= " AND ROLE_TYPE = 'CDR'";
                break;
    
                case "CDS":
                    $sql .= " AND ROLE_TYPE = 'CDS'";
                break;
    
                case "CDV":
                    $sql .= " AND ROLE_TYPE = 'CDV'";
                break;
    
                case "COMPTABLE":
                    $sql .= " AND ROLE_TYPE = 'COMPTABLE'";
                break;
    
                default:
                break;
            }

            $res = Database::query($sql);
            $ln = Database::fetchArray($res);

            if($ln["HASROLE"] != 0)
            {
                $message = "Un utilisateur avec la m&ecirc;me adresse email existe déj&agrave; pour le r&ocirc;le ".$d["ROLE_TYPE"];
                throw new Exception($message);
            }
        }
    }

    /**
     * Fonction qui renvoie le formulaire pour supprimer un utilisateur
     *
     * @param array $d - Tableau contenant le USER_NUM et le ROLE_TYPE
     * @return string $html - Le formulaire HTML
     */
    static function getFormDelete($d)
    {
        $sqlInfos = "
            SELECT * FROM user
            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            WHERE user.USER_NUM = ".$d["USER_NUM"]."
            AND ROLE_TYPE = '".$d["ROLE_TYPE"]."'
        ";
        $resInfos = Database::query($sqlInfos);
        $lnInfos = Database::fetchArray($resInfos);

        switch($d["ROLE_TYPE"])
        {
            case "COMPTABLE":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM, CAB_NUM
                    FROM stationcc
                    JOIN comptable ON comptable.CC_NUM = stationcc.CC_NUM
                    JOIN userhasrole ON userhasrole.ROLE_NUM = stationcc.CC_NUM
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $CAB_NUM = $lnNbDossiers["CAB_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM
                        WHERE CAB_NUM = ".$CAB_NUM."
                        AND ROLE_TYPE = 'COMPTABLE'
                        AND CC_NUM <> ".$ROLE_NUM."
                        AND CC_IS_ADMIN = 0
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            case "CDR":
                    $sqlNbDossiers = "
                        SELECT COUNT(*) AS NB, ROLE_NUM
                        FROM lieu
                        JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefRegion
                        WHERE USER_NUM = ".$d["USER_NUM"]."
                        AND ROLE_TYPE = 'CDR'
                    ";
                    $resNbDossiers = Database::query($sqlNbDossiers);
                    $lnNbDossiers = Database::fetchArray($resNbDossiers);
                    $nbDossiers = $lnNbDossiers["NB"];
                    $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                    $options = array();
                    if($nbDossiers > 0)
                    {
                        $sqlOptions = "
                            SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                            FROM user
                            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                            WHERE ROLE_TYPE = 'CDR'
                            AND ROLE_NUM <> ".$ROLE_NUM."
                            ORDER BY USER_NOM
                        ";
                        $resOptions = Database::query($sqlOptions);
                        while($lnOptions = Database::fetchArray($resOptions))
                        {
                            $options[] = array(
                                "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                                "USER_NOM" => $lnOptions["USER_NOM"],
                                "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                            );
                        }
                    }
                break;

            case "CDS":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM
                    FROM lieu
                    JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefSecteur
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                    AND ROLE_TYPE = 'CDS'
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        WHERE ROLE_TYPE = 'CDS'
                        AND ROLE_NUM <> ".$ROLE_NUM."
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            case "CDV":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM
                    FROM lieu
                    JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefVente
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                    AND ROLE_TYPE = 'CDV'
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        WHERE ROLE_TYPE = 'CDV'
                        AND ROLE_NUM <> ".$ROLE_NUM."
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            default:
                break;
        }


        $html = "
            <div id='deleteUser'>
                <form id='formDeleteUser'>
                    <p style='text-align: center'>Voulez-vous supprimer l'utilisateur ".$lnInfos["USER_NOM"]." ".$lnInfos["USER_PRENOM"]." ?</p>
                    <input type='hidden' name='USER_NUM' id='USER_NUM' value='".$lnInfos["USER_NUM"]."'>
                    <input type='hidden' name='ROLE_TYPE' id='ROLE_TYPE' value='".$d["ROLE_TYPE"]."'>
                    <input type='hidden' name='ROLE_NUM' id='ROLE_NUM' value='".$lnInfos["ROLE_NUM"]."'>
                    ";

        // Si l'utilisateur a des dossiers sur l'appli, on demande à qui les réaffecter
        if($nbDossiers > 0)
        {
            $html .= "
                    <input type='hidden' name='HAS_DOSSIERS' id='HAS_DOSSIERS' value='1'>
                    <p style='color:red;font-weight:bold;'>ATTENTION</p>
                    <p>Cet utilisateur poss&egrave;de ".$nbDossiers." dossier(s) dans My Report, merci de choisir &agrave; l'aide du sélecteur en dessous la personne à qui réattribuer l'ensemble de ses dossiers :</p>
                    <select name='NEW_USER' id='NEW_USER' style='display:block;margin:auto;'>
                        <option value='' disabled='disabled' selected='selected'>Choisissez un utilisateur</option>
            ";

            foreach($options as $option)
            {
                $html .= "<option value='".$option["ROLE_NUM"]."'>".$option["USER_NOM"]." ".$option["USER_PRENOM"]."</option>";
            }

            $html .= "</select>";
        }

        $html .= "
                </form>
            </div>
        ";

        return array(
            "hasDossiers" => $nbDossiers > 0 ? true : false,
            "html" => utf8_encode($html)
        );
    }

    /**
     * Fonction qui va supprimer un utilisateur de la table user, userhasrole et sa table correspondante
     *
     * @param $d array - Tableau contenant le USER_NUM, ROLE_TYPE ainsi que les infos si il avait des dossiers
     * @return $return array - Tableau informant de la réussite ou non de la suppression
     */
    static function deleteUser($d)
    {
        $datas = $d["datas"];
        $User = new User;

        // On récupère l'utilisateur sur My Report
        $sqlUser = "
            SELECT *
            FROM user
            WHERE USER_NUM = '".$datas["USER_NUM"]."'
        ";
        $resUser = Database::query($sqlUser);
        $user = Database::fetchArray($resUser);

        try
        {
            // Réaffectation des dossiers à l'utilisateur choisi
            if($datas["HAS_DOSSIERS"] == 1 && $datas["NEW_USER"])
            {
                $param = array(
                    "datas" => array(
                        "ROLE_TYPE" => $datas["ROLE_TYPE"],
                        "NEW_USER" => $datas["NEW_USER"],
                        "ROLE_NUM" => $datas["ROLE_NUM"]
                    ),
                    "fromDelete" => true
                );

                $resDossiers = self::reaffectDossiers($param);
                if($resDossiers["error"])
                    throw new Exception("Erreur lors de la réaffectation des dossiers");
            }

            // Suppression dans la table de correspondance
            switch($datas["ROLE_TYPE"])
            {
                case "COMPTABLE":
                    $sqlDeleteTable = "
                        DELETE FROM comptable WHERE CC_NUM = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression de l'utilisateur dans la table comptable");
                    break;

                case "STATION":
                    $sqlDeleteTable = "
                        DELETE FROM gerant WHERE GER_NUM = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression de l'utilisateur dans la table gérant");
                    break;

                case "CDR":
                    $sqlDeleteTable = "
                        DELETE FROM chefregion WHERE codeChefRegion = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression de l'utilisateur dans la table chefregion");
                    break;

                case "CDS":
                    $sqlDeleteTable = "
                        DELETE FROM chefsecteur WHERE codeChefSecteur = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression l'utilisateur dans la table chefsecteur");
                    break;

                case "CDV":
                    $sqlDeleteTable = "
                        DELETE FROM chefvente WHERE codeChefVente = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression l'utilisateur dans la table chefvente");
                    break;

                case "SIEGE":
                    $sqlDeleteTable = "
                        DELETE FROM agip WHERE AG_NUM = ".$datas["ROLE_NUM"]."
                    ";
                    $resDeleteTable = Database::query($sqlDeleteTable);
                    if(!$resDeleteTable)
                        throw new Exception("Erreur lors de la suppression de l'utilisateur dans la table agip");
                    break;

                default:
                    break;
            }

            // Suppression dans la table userhasrole
            $sqlDeleteRole = "
                DELETE FROM userhasrole
                WHERE ROLE_NUM = ".$datas["ROLE_NUM"]."
                AND USER_NUM = ".$datas["USER_NUM"]."
            ";
            $resDeleteRole = Database::query($sqlDeleteRole);
            if(!$resDeleteRole)
                throw new Exception("Impossible de supprimer l'utilisateur dans la table userhasrole");

            // Si plus de ligne dans la table userhasrole, suppression dans la table user
            $sqlSearch = "
                SELECT COUNT(*) AS NB
                FROM userhasrole
                WHERE USER_NUM = ".$datas["USER_NUM"]."
            ";
            $resSearch = Database::query($sqlSearch);
            $lnSearch = Database::fetchArray($resSearch);
            $nbRoles = $lnSearch["NB"];

            if($nbRoles == "0")
            {
                $sqlDeleteUser = "
                    DELETE FROM user WHERE USER_NUM = ".$datas["USER_NUM"]."
                ";
                $resDeleteUser = Database::query($sqlDeleteUser);
                if(!$resDeleteUser)
                    throw new Exception("Impossible de supprimer l'utilisateur dans la table user");

                // Suppression du role sur le serveur d'authentification seulement dans le cas où l'utilisateur n'a plus de rôle sur My Report
                $datasLockers = array(
                    "role" => self::$transcoRole[$datas["ROLE_TYPE"]]
                );
                $resLockers = $User->removeAccess($user["USER_LOCKERS_ID"], $datasLockers);

                if(!$resLockers || $resLockers["success"] == false)
                    throw new Exception("Impossible de supprimer le r&ocirc;le utilisateur sur le serveur d'authentification");
            }

            return array(
                "data" => array(
                    "information" => utf8_encode("L'utilisateur a été supprimé avec succés")
                )
            );

        }
        catch(Exception $e)
        {
            return array(
                "error" => array(
                    "message" => utf8_encode($e->getMessage())
                )
            );
        }
    }

    /**
     * Fonction qui va renvoyer un formulaire HTML pour réaffecter les dossiers d'une personne à un autre
     *
     * @param $d array - Tableau contenant les infos de la personne à qui on souhaite enlever les dossiers
     * @return $html string - Formulaire html sous forme de chaine de caractère
     */
    static function getFormReaffectDossiers($d)
    {
        $html = "";

        $sqlInfos = "
            SELECT *
            FROM user
            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            WHERE user.USER_NUM = ".$d["USER_NUM"]."
            AND ROLE_TYPE = '".$d["ROLE_TYPE"]."'
        ";
        $resInfos = Database::query($sqlInfos);
        $lnInfos = Database::fetchArray($resInfos);

        switch($d["ROLE_TYPE"])
        {
            case "COMPTABLE":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM, CAB_NUM
                    FROM stationcc
                    JOIN comptable ON comptable.CC_NUM = stationcc.CC_NUM
                    JOIN userhasrole ON userhasrole.ROLE_NUM = stationcc.CC_NUM
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $CAB_NUM = $lnNbDossiers["CAB_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM
                        WHERE CAB_NUM = ".$CAB_NUM."
                        AND ROLE_TYPE = 'COMPTABLE'
                        AND CC_NUM <> ".$ROLE_NUM."
                        AND CC_IS_ADMIN = 0
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            case "CDR":
                    $sqlNbDossiers = "
                        SELECT COUNT(*) AS NB, ROLE_NUM
                        FROM lieu
                        JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefRegion
                        WHERE USER_NUM = ".$d["USER_NUM"]."
                        AND ROLE_TYPE = 'CDR'
                    ";
                    $resNbDossiers = Database::query($sqlNbDossiers);
                    $lnNbDossiers = Database::fetchArray($resNbDossiers);
                    $nbDossiers = $lnNbDossiers["NB"];
                    $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                    $options = array();
                    if($nbDossiers > 0)
                    {
                        $sqlOptions = "
                            SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                            FROM user
                            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                            WHERE ROLE_TYPE = 'CDR'
                            AND ROLE_NUM <> ".$ROLE_NUM."
                            ORDER BY USER_NOM
                        ";
                        $resOptions = Database::query($sqlOptions);
                        while($lnOptions = Database::fetchArray($resOptions))
                        {
                            $options[] = array(
                                "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                                "USER_NOM" => $lnOptions["USER_NOM"],
                                "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                            );
                        }
                    }
                break;

            case "CDS":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM
                    FROM lieu
                    JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefSecteur
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                    AND ROLE_TYPE = 'CDS'
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        WHERE ROLE_TYPE = 'CDS'
                        AND ROLE_NUM <> ".$ROLE_NUM."
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            case "CDV":
                $sqlNbDossiers = "
                    SELECT COUNT(*) AS NB, ROLE_NUM
                    FROM lieu
                    JOIN userhasrole ON userhasrole.ROLE_NUM = lieu.codeChefVente
                    WHERE USER_NUM = ".$d["USER_NUM"]."
                    AND ROLE_TYPE = 'CDV'
                ";
                $resNbDossiers = Database::query($sqlNbDossiers);
                $lnNbDossiers = Database::fetchArray($resNbDossiers);
                $nbDossiers = $lnNbDossiers["NB"];
                $ROLE_NUM = $lnNbDossiers["ROLE_NUM"];
                $options = array();
                if($nbDossiers > 0)
                {
                    $sqlOptions = "
                        SELECT ROLE_NUM, USER_NOM, USER_PRENOM
                        FROM user
                        JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
                        WHERE ROLE_TYPE = 'CDV'
                        AND ROLE_NUM <> ".$ROLE_NUM."
                        ORDER BY USER_NOM
                    ";
                    $resOptions = Database::query($sqlOptions);
                    while($lnOptions = Database::fetchArray($resOptions))
                    {
                        $options[] = array(
                            "ROLE_NUM" => $lnOptions["ROLE_NUM"],
                            "USER_NOM" => $lnOptions["USER_NOM"],
                            "USER_PRENOM" => $lnOptions["USER_PRENOM"]
                        );
                    }
                }
                break;

            default:
                break;
        }

        if($nbDossiers > 0)
        {
            $html.= "
                <div>
                    <form id='formReaffectDossiers'>
                        <center>
                            <input type='hidden' name='ROLE_TYPE' value='".$d["ROLE_TYPE"]."'>
                            <input type='hidden' name='ROLE_NUM' value='".$lnInfos["ROLE_NUM"]."'>
                            <p style='white-space: pre-wrap;'>".$lnInfos["USER_NOM"]." ".$lnInfos["USER_PRENOM"]." poss&egrave;de actuellement ".$nbDossiers." dossier(s), &agrave; qui souhaitez-vous les réaffecter :</p>
                            <select name='NEW_USER' id='NEW_USER'>
                                <option selected='selected' disabled='disabled'>Choisissez un utilisateur</option>
            ";

            foreach($options as $option)
            {
                $html .= "<option value='".$option["ROLE_NUM"]."'>".$option["USER_NOM"]." ".$option["USER_PRENOM"]."</option>";
            }

            $html .= "
                        </select>
                        </center>
                    </form>
                </div>
            ";
        }
        else
        {
            $html .= "
                <div>
                    <center>".$lnInfos["USER_NOM"]." ".$lnInfos["USER_PRENOM"]." ne poss&egrave;de pas de dossier sur My Report.</center>
                </div>
            ";
        }

        return array(
            "html" => utf8_encode($html),
            "hasDossiers" => $nbDossiers == 0 ? 0 : 1
        );
    }

    /**
     * Fonction qui va réattribuer les dossiers à une autre personne
     *
     * @param $d array - Tableau contenant les infos de la personne et celle à qui on réattribue les dossiers
     * $d = array(
     *      "datas" => array(
     *          "NEW_USER" => int,
     *          "ROLE_NUM" => int,
     *          "ROLE_TYPE" => string
     *      )
     * )
     * @return $return
     */
    static function reaffectDossiers($d)
    {
        $datas = $d["datas"];

        try
        {
            switch($datas["ROLE_TYPE"])
            {
                case "COMPTABLE":
                    $sqlUpdate = "
                        UPDATE stationcc
                        SET CC_NUM = ".$datas["NEW_USER"]."
                        WHERE CC_NUM = ".$datas["ROLE_NUM"]."
                    ";
                    $resUpdate = Database::query($sqlUpdate);
                    if(!$resUpdate)
                        throw new Exception("Erreur lors de la réaffection des dossiers");
                    break;

                case "CDR":
                    $sqlUpdate = "
                        UPDATE lieu
                        SET codeChefRegion = ".$datas["NEW_USER"]."
                        WHERE codeChefRegion = ".$datas["ROLE_NUM"]."
                    ";
                    $resUpdate = Database::query($sqlUpdate);
                    if(!$resUpdate)
                        throw new Exception("Erreur lors de la réaffectation des dossiers");
                    break;

                case "CDS":
                    $sqlUpdate = "
                        UPDATE lieu
                        SET codeChefSecteur = ".$datas["NEW_USER"]."
                        WHERE codeChefSecteur = ".$datas["ROLE_NUM"]."
                    ";
                    $resUpdate = Database::query($sqlUpdate);
                    if(!$resUpdate)
                        throw new Exception("Erreur lors de la réaffectation des dossiers");
                    break;
                    break;

                case "CDV":
                    $sqlUpdate = "
                        UPDATE lieu
                        SET codeChefVente = ".$datas["NEW_USER"]."
                        WHERE codeChefVente = ".$datas["ROLE_NUM"]."
                    ";
                    $resUpdate = Database::query($sqlUpdate);
                    if(!$resUpdate)
                        throw new Exception("Erreur lors de la réaffectation des dossiers");
                    break;

                default:
                    break;
            }

            if(isset($d["fromDelete"]) && $d["fromDelete"])
            {
                return true;
            }
            else
            {
                return array(
                    "data" => array(
                        "information" => utf8_encode("Les dossiers ont été réattribués avec succés")
                    )
                );
            }
        }
        catch(Exception $e)
        {
            return array(
                "error" => array(
                    "message" => utf8_encode($e->getMessage())
                )
            );
        }
    }

    /**
     * Fonction qui va retourner la liste des gérants d'une station
     *
     * @param $d array
     * @return $return
     */
    static function getListeGerants($d)
    {
        $datas = array();
        $sql = "
            SELECT user.*, ROLE_NUM, GER_NUMBER
            FROM gerant
            JOIN userhasrole ON userhasrole.ROLE_NUM = gerant.GER_NUM AND ROLE_TYPE = 'STATION'
            JOIN user ON user.USER_NUM = userhasrole.USER_NUM
            WHERE STA_NUM = ".$d["STA_NUM"]."
        ";
        $res = Database::query($sql);
        while($ln = Database::fetchArray($res))
        {
            foreach($ln as &$champ)
            {
                $champ = utf8_encode($champ);
            }

            $datas[] = $ln;
        }

        return array("datas" => $datas);
    }

    /**
     * Fonction qui retourne la liste des comptables d'un cabinet
     *
     * @param $d array
     * @return $return array
     */
    static function listComptables($d)
    {
        $LIMIT = 20;
        $CAB_NUM = $_SESSION["logedVar"]["CAB_NUM"];
        $page = $d["page"];
        $offset = ($page - 1) * $LIMIT;

        $sql = "
            SELECT user.*, userhasrole.ROLE_NUM, COUNT(STA_NUM) AS DOSSIERS, comptable.*
            FROM user
            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM
            LEFT JOIN stationcc ON stationcc.CC_NUM = comptable.CC_NUM
            WHERE ROLE_TYPE = 'COMPTABLE'
            AND CAB_NUM = ".$CAB_NUM."
            GROUP BY ROLE_NUM
            ORDER BY USER_NOM
            LIMIT ".$LIMIT." OFFSET ".$offset."
        ";
        $res = Database::query($sql);
        while($ln = Database::fetchArray($res))
        {
            foreach($ln as &$champ)
            {
                $champ = utf8_encode($champ);
            }

            $resultats[] = $ln;
        }

        $sql = "
            SELECT COUNT(*) AS results
            FROM user
            JOIN userhasrole ON userhasrole.USER_NUM = user.USER_NUM
            JOIN comptable ON comptable.CC_NUM = userhasrole.ROLE_NUM
            WHERE ROLE_TYPE = 'COMPTABLE'
            AND CAB_NUM = ".$CAB_NUM."
        ";
        $res = Database::query($sql);
        while($ln = Database::fetchArray($res))
        {
            $nbResults = $ln["results"];
        }

        $nbPage = ceil($nbResults / $LIMIT);

        $return = array(
            "datas" => $resultats,
            "page" => $nbPage,
            "nombre" => $nbResults
        );

        return $return;
    }
}

?>