<?php

namespace Controller;

use Classes\Http\Response;
use Services\BilanService;

class BilanController extends AbstractController
{

    private BilanService $bilanService;

    public function __construct(BilanService $bilanService)
    {
        $this->bilanService = $bilanService;
    }

    public function index(): Response
    {
        $bilan = $this->bilanService->getBilan();
        
        return $this->render('Bilan/index.html.twig', [
            'mesPostesActif' => $bilan['mesPostesActif'],
            'mesPostesPassif' => $bilan['mesPostesPassif']
        ]);
    }
}
