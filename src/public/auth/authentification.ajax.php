<?php

require_once __DIR__."/../auth/classes/Auth.class.php";
require_once __DIR__."/../auth/classes/User.class.php";

// Instantiate Authentification class
$Authentification = new Auth;

// Instantiate User class
$User = new User;

$response = array();

try
{
    // Instantiate response
    $code = 200;
    $message = "";

    switch($_POST["action"])
    {
        /* AUTH REQUEST *************************/

        // Login request
        case "login":
            $response = $Authentification->login(array(
                "email"    => $_POST["email"],
                "password" => $_POST["password"]
            ));
        break;

        // User want mail to reset password
        case "mailPassword":
            $response = $Authentification->mailPassword(array(
                "email" => $_POST["email"]
            ));
        break;

        // User reset password
        case "resetPassword":
            $response = $Authentification->resetPassword(array(
                "password" => $_POST["password"],
                "token"    => $_POST["token"]
            ));
        break;

        // User accept a file
        case "acceptFile":
            $response = $User->acceptFile(array(
                "fileId" => $_POST["fileId"],
                "token"  => $_POST["token"]
            ));
        break;

        /* USER REQUEST *************************/

        // Create new user on the app
        case "createUser":
        // Update user on the app
        case "updateUser":
        // Delete user on the app
        case "deleteUser":
        break;

        /* DEFAULT REQUEST **********************/
        default:
            $reponse = array(
                "info" => array(
                    "code" => 404,
                    "message" => "Method not found"
                )
            );
        break;
    }

    $code = $response["info"]["code"];
    $message = $response["info"]["message"];
}
catch(Exception $e)
{
    $code = 500;
    $message = "An error occured";
    $response = array(
        "success" => false,
        "data"    => array(),
        "info"    => array(
            "message" => $e->getMessage(),
            "code"    => $code
        )
    );
}

header("HTTP/1.0 ".$code." ".$message);
header("Content-Type: application/json");
echo json_encode($response);
