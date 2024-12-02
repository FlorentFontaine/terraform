<?php

namespace Controller;

use Classes\Http\Request;
use Classes\Http\Response;
use Services\DetailPosteService;

class DetailPosteController extends AbstractController
{

    private DetailPosteService $detailPosteService;

    public function __construct(DetailPosteService $detailPosteService)
    {
        $this->detailPosteService = $detailPosteService;
    }

    public function show(Request $request,  array $params = []): Response
    {
        $params["produits"] = $request->getParams()["produits"];
        $detailPoste = $this->detailPosteService->getDetail($params);

        return $this->render('DetailPoste/show.html.twig', [
            'detailPoste' => $detailPoste
        ]);
    }
}
