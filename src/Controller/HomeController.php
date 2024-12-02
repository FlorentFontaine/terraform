<?php

namespace Controller;

use Classes\Http\Response;

class HomeController extends AbstractController
{
    public function home(): Response
    {
         // Initialize the session.
         // If you are using session_name("something"), don't forget it now!
         session_start();

         // Unset all the session variables.
         $_SESSION = array();

         // If it's desired to kill the session, also delete the session cookie.
         // Note: This will destroy the session, and not just the session data!
         if (ini_get("session.use_cookies")) {
             $params = session_get_cookie_params();
             setcookie(
                 session_name(),
                 '',
                 time() - 42000,
                 $params["path"],
                 $params["domain"],
                 $params["secure"],
                 $params["httponly"]
             );
         }

         // Finally, destroy the session.
         session_destroy();

         //Redirection vers la connexion avec le serveur
         header("Location: ./auth/login.php");
         exit();
    }

    public function flash()
    {
        $this->session->setFlash('success', 'YOLO !');
        header('Location: /');
        exit();
    }
}
