<?php

namespace Controller;

use Classes\Http\Response;
use Controller\AbstractController;
use Repositories\LieuRepository;

class LieuController extends AbstractController
{
    public function index(): Response
    {
        // Deconnexion de tout dossier lors de la visite de cette page
        $_SESSION["station_STA_NUM"] = false;
        $_SESSION["station_DOS_NUM"] = false;
        $_SESSION["station_LIE_NUM"] = false;
        $_SESSION["inLIE_NUM"] = false;
        $_SESSION["MoisHisto"] = false;
        $_SESSION["station_STA_MAINTENANCE"] = false;

        return $this->render('Lieu/index.html.twig', [
            'lieux' => (new LieuRepository())->getLieux()
        ]);
    }
}
