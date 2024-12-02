<?php

require_once __DIR__ . '/../../../Init/bootstrap.php';
require_once __DIR__ . "/../../dbClasses/AccesDonnees.php";

use Classes\DB\Database;
use CICD\Lockers\Services\Client;

class Auth
{
    protected $lockers = null;

    /**
     * Contructor
     */
    public function __construct()
    {
        $this->lockers = new Client;
    }

    /**
     * Send email for password reseting
     */
    public function mailPassword($params)
    {
        return $this->lockers->mailPassword($params);
    }

    /**
     * Reset password
     */
    public function resetPassword($params)
    {
        return $this->lockers->resetPassword($params);
    }

    /**
     * Login
     */
    public function login($params)
    {
        return $this->lockers->login($params);
    }

    /**
     * Attempt login and retrieve user
     */
    public function attemptLogin()
    {
        // Synchronisation des données avec Lockers
        $this->synchronizeDatas();

        // Mise en session de l'utilisateur connecté
        $this->setUserSession();

        return true;
    }

    /**
     * Synchronize auth server datas to app database
     */
    public function synchronizeDatas()
    {
        // Récupération de l'utilisateur depuis Lockers
        $user = $this->lockers->profile();
        $data = $user["data"];

        // S'il s'agit d'un admin, on passe cette étape
        if ($data["admin"]) {
            return;
        }

        if (!empty($data)) {
            $email = $data["email"];
            $firstName = utf8_decode($data["firstName"]);
            $lastName = utf8_decode($data["lastName"]);
            $lockersId = $data["id"];

            // MàJ des infos de l'utilisateur
            $sql = "
                UPDATE user
                SET
                    USER_NOM = '$lastName',
                    USER_PRENOM = '$firstName',
                    USER_MAIL = '$email'
                WHERE USER_LOCKERS_ID = '$lockersId'
            ";

            if (Database::query($sql)) {
                // Récupération de l'USER_NUM
                $sql = "
                    SELECT USER_NUM
                    FROM user
                    WHERE USER_LOCKERS_ID = '$lockersId'
                    LIMIT 1
                ";
                Database::query($sql);
                $ln = Database::fetchArray();
                $USER_NUM = $ln["USER_NUM"];

                // Récupération de la liste des roles de l'utilisateur
                $sql = "
                    SELECT *
                    FROM userhasrole
                    WHERE USER_NUM = '$USER_NUM'
                ";
                Database::query($sql);
                $userHasRoles = array();
                while ($ln = Database::fetchArray()) {
                    $userHasRoles[] = $ln;
                }

                // MàJ des infos dans la table finale (chefregion, chefsecteur, gerant, comptable, total)
                foreach ($userHasRoles as $userHasRole) {
                    switch ($userHasRole["ROLE_TYPE"]) {
                        case "CDR":
                            $sql = "
                                UPDATE chefregion
                                SET Nom = '$lastName', Prenom = '$firstName', E_Mail = '$email'
                                WHERE codeChefRegion = '{$userHasRole["ROLE_NUM"]}'
                            ";
                            Database::query($sql);
                            break;
                        case "CDS":
                            $sql = "
                                UPDATE chefsecteur
                                SET Nom = '$lastName', Prenom = '$firstName', E_Mail = '$email'
                                WHERE codeChefSecteur = '{$userHasRole["ROLE_NUM"]}'
                            ";
                            Database::query($sql);
                            break;
                        case "STATION":
                            $sql = "
                                UPDATE gerant
                                SET GER_NOM = '$lastName', GER_PRENOM = '$firstName', GER_MAIL = '$email'
                                WHERE GER_NUM = '{$userHasRole["ROLE_NUM"]}'
                            ";
                            Database::query($sql);
                            break;
                        case "COMPTABLE":
                            $sql = "
                                UPDATE comptable
                                SET CC_NOM = '" . $lastName . " " . $firstName . "', CC_MAIL = '$email'
                                WHERE CC_NUM = '{$userHasRole["ROLE_NUM"]}'
                            ";
                            Database::query($sql);
                            break;
                        case "SIEGE":
                            $sql = "
                                UPDATE agip
                                SET Nom = '$lastName', Prenom = '$firstName', AG_MAIL = '$email'
                                WHERE AG_NUM = '{$userHasRole["ROLE_NUM"]}'
                            ";
                            Database::query($sql);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }

    /**
     * Put user information in $_SESSION
     */
    public function setUserSession()
    {
        // Récupération de l'utilisateur
        $user = $this->lockers->profile();

        // Session de l'application
        $_SESSION["User_Mail"] = $user["data"]["email"];
        $_SESSION["LOCKERS_MAIL"] = $user["data"]["email"];

        $id = $user["data"]["id"];

        // Cas d'un utilisateur pas ADMIN CICD
        if (!$user["data"]["admin"]) {
            $sql = " SELECT userhasrole.*, USER_NOM, USER_PRENOM, USER_MAIL
                    FROM userhasrole
                    JOIN user ON user.USER_NUM = userhasrole.USER_NUM
                    WHERE USER_LOCKERS_ID = '" . $id . "' ";
            Database::query($sql);
            $ln = Database::fetchArray();

            $_SESSION["Utilisateur"] = array(
                "Nom" => $ln["USER_NOM"],
                "Prenom" => $ln["USER_PRENOM"],
                "Mail" => $ln["USER_MAIL"],
                "Type" => $ln["ROLE_TYPE"]
            );

            switch ($ln["ROLE_TYPE"]) {
                case "CDR":
                    $_SESSION["loged"] = "cdr";
                    $_SESSION["TYPE_USER"] = "cdr";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT chefregion.* FROM chefregion
                        WHERE codeChefRegion = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                case "CDS":
                    $_SESSION["loged"] = "cds";
                    $_SESSION["TYPE_USER"] = "cds";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT chefsecteur.* FROM chefsecteur
                        WHERE codeChefSecteur = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                case "CDV":
                    $_SESSION["loged"] = "cdv";
                    $_SESSION["TYPE_USER"] = "cdv";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT chefvente.* FROM chefvente
                        WHERE codeChefVente = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                case "COMPTABLE":
                    $_SESSION["loged"] = "comptable";
                    $_SESSION["TYPE_USER"] = "comptable";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT comptable.*, cabinet.*, balanceformat.* FROM comptable
                        JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
                        LEFT JOIN balanceformat ON balanceformat.BAF_NUM = cabinet.BAF_NUM
                        WHERE CC_NUM = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                case "STATION":
                    $_SESSION["loged"] = "station";
                    $_SESSION["TYPE_USER"] = "station";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT gerant.* FROM gerant
                        JOIN station ON station.STA_NUM = gerant.STA_NUM
                        WHERE GER_NUM = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                case "SIEGE":
                    $_SESSION["loged"] = "agip";
                    $_SESSION["TYPE_USER"] = "agip";
                    $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                    $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                    $sql = "
                        SELECT agip.* FROM agip
                        WHERE AG_NUM = '{$ln["ROLE_NUM"]}'
                    ";
                    break;

                default:
                    break;
            }

            Database::query($sql);
            $ln = Database::fetchArray();

            $_SESSION["logedVar"] = $ln;
        } else {
            $_SESSION["Utilisateur"] = array(
                "Nom" => utf8_decode($user["data"]["lastName"]),
                "Prenom" => utf8_decode($user["data"]["firstName"]),
                "Mail" => $user["data"]["email"],
                "Type" => "ADMIN",
                "LockersAdmin" => true
            );

            $_SESSION["loged"] = "agip";
            $_SESSION["logedVar"] = array(
                "AG_NUM" => null,
                "AG_MAIL" => $user["data"]["email"],
                "AG_ISADMIN" => 1,
                "AG_MDP" => "",
                "Nom" => utf8_decode($user["data"]["lastName"]),
                "Prenom" => utf8_decode($user["data"]["firstName"]),
                "AG_TYPE" => "ADMIN",
                "MAJ_MDP" => "0000-00-00"
            );
        }
    }

    /**
     * Login as function
     */
    public function loginAs($lockersId, $roleNum = null, $roleType = null)
    {
        $sql = " SELECT userhasrole.*, USER_MAIL, USER_NOM, USER_PRENOM
                FROM userhasrole
                JOIN user ON user.USER_NUM = userhasrole.USER_NUM
                WHERE USER_LOCKERS_ID = '$lockersId' ";

        if ($roleNum) {
            $sql .= " AND ROLE_NUM = '$roleNum'";
        }

        if ($roleType) {
            $sql .= " AND ROLE_TYPE = '$roleType'";
        }

        Database::query($sql);
        $ln = Database::fetchArray();

        $_SESSION["User_Mail"] = $ln["USER_MAIL"];

        $_SESSION["Utilisateur"] = array(
            "Nom" => $ln["USER_NOM"],
            "Prenom" => $ln["USER_PRENOM"],
            "Mail" => $ln["USER_MAIL"],
            "Type" => $ln["ROLE_TYPE"]
        );

        switch ($ln["ROLE_TYPE"]) {
            case "CDR":
                $_SESSION["loged"] = "cdr";
                $_SESSION["TYPE_USER"] = "cdr";
                $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT chefregion.* FROM chefregion
                    WHERE codeChefRegion = '{$ln["ROLE_NUM"]}'
                ";
                break;

            case "CDV":
                $_SESSION["loged"] = "cdv";
                $_SESSION["TYPE_USER"] = "cdv";
                $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT chefvente.* FROM chefvente
                    WHERE codeChefVente = '{$ln["ROLE_NUM"]}'
                ";
                break;

            case "CDS":
                $_SESSION["loged"] = "cds";
                $_SESSION["TYPE_USER"] = "cds";
                $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT chefsecteur.* FROM chefsecteur
                    WHERE codeChefSecteur = '{$ln["ROLE_NUM"]}'
                ";
                break;

            case "COMPTABLE":
                $_SESSION["loged"] = "comptable";
                $_SESSION["TYPE_USER"] = "comptable";
                $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT comptable.*, cabinet.*, balanceformat.* FROM comptable
                    JOIN cabinet ON cabinet.CAB_NUM = comptable.CAB_NUM
                    LEFT JOIN balanceformat ON balanceformat.BAF_NUM = cabinet.BAF_NUM
                    WHERE CC_NUM = '{$ln["ROLE_NUM"]}'
                ";
                break;

            case "STATION":
                $_SESSION["loged"] = "station";
                $_SESSION["TYPE_USER"] = "station";
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT gerant.* FROM gerant
                    WHERE GER_NUM = '{$ln["ROLE_NUM"]}'
                ";
                break;

            case "SIEGE":
                $_SESSION["loged"] = "agip";
                $_SESSION["TYPE_USER"] = "agip";
                $_SESSION["ID_USER"] = $ln["ROLE_NUM"];
                $_SESSION["USER_NUM"] = $ln["USER_NUM"];
                $sql = "
                    SELECT agip.* FROM agip
                    WHERE AG_NUM = '{$ln["ROLE_NUM"]}'
                ";
                break;

            default:
                break;
        }

        Database::query($sql);
        $ln = Database::fetchArray();

        $_SESSION["logedVar"] = $ln;
        $_SESSION["logedVar"]["TYPE_USER"] = $_SESSION["TYPE_USER"];
        $_SESSION["logedVar"]["ID_USER"] = $_SESSION["ID_USER"];
    }

    /**
     *  Check if user is connected
     */
    public function checkSession()
    {
        try {
            $response = $this->lockers->refreshToken();

            if ($response && $response["success"]) {
                return;
            }
            else {
                $this->logout();
                header("Location: ../");
                exit();
            }
        } catch (Exception $e) {
            session_destroy();
            header("Location: ../");
            exit();
        }
    }

    /**
     * Logout function
     */
    public function logout()
    {
        $response = $this->lockers->logout();

        if ($response["success"]) {
            session_destroy();
        }
    }

    /**
     * Clean session without remove Lockers information
     */
    public function cleanSession()
    {
        $lockersSessionName = getenv('APP_LOCKERS_USER_SESSION_NAME') ? getenv('APP_LOCKERS_USER_SESSION_NAME') : "LOCKERS_USER";
        $tmpSession = $_SESSION[$lockersSessionName];

        unset($_SESSION);
        session_destroy();

        session_start();

        $_SESSION[$lockersSessionName] = $tmpSession;
    }
}
