<?php

// Inverser l'effet de magic_quotes_gpc
if(get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

require_once __DIR__."/../Utilisateurs/Utilisateurs.class.php";

if($_POST["action"])
{
    switch($_POST["action"])
    {
        case "getUsers":
            $response = Utilisateurs::getUsers($_POST);
            break;

        case "getForm":
            $response = Utilisateurs::getForm($_POST);
            break;

        case "checkUniqueEmail":
            $response = Utilisateurs::checkUniqueEmail($_POST);
            break;

        case "saveUser":
            $response = Utilisateurs::saveUser($_POST);
            break;

        case "getFormDelete":
            $response = Utilisateurs::getFormDelete($_POST);
            break;

        case "deleteUser":
            $response = Utilisateurs::deleteUser($_POST);
            break;

        case "getFormReaffectDossiers":
            $response = Utilisateurs::getFormReaffectDossiers($_POST);
            break;

        case "reaffectDossiers":
            $response = Utilisateurs::reaffectDossiers($_POST);
            break;

        // Spécifique pour les gérants
        case "getListeGerants":
            $response = Utilisateurs::getListeGerants($_POST);
            break;

        // Spécifique pour les gérants
    case "getListeAssistants":
            $response = Utilisateurs::getListeAssistants($_POST);
            break;

        // Spécifique pour les comptables
        case "listComptables":
            $response = Utilisateurs::listComptables($_POST);
            break;

        default:
            break;
    }

    echo json_encode($response);
}

?>