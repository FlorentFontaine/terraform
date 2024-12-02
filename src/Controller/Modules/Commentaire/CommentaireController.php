<?php

namespace Controller\Modules\Commentaire;

use Classes\Http\Request;
use Controller\AbstractController;
use Mpdf\Mpdf;
use Services\Modules\Commentaire\CommentaireService;
use Services\StationService;

class CommentaireController extends AbstractController
{
    private CommentaireService $commentaireService;

    private StationService $stationService;


    public function __construct(CommentaireService $commentaireService, StationService $stationService)
    {
        $this->commentaireService = $commentaireService;
        $this->stationService = $stationService;
    }

    public function index(Request $request, array $params = [])
    {

        foreach ($this->commentaireService->getCommentairesStructureBySection($params["section"]) as $key => $value) {
            $label = substr($value["CMS_ORDRE"], 0, 1) . ". " . $value["CMS_ONGLET"];
            $params["commentaires"]["structures"][$label][$key] = $value;

            if($value["CMS_SLUG"]) {
                $this->setSpecificityByContent($params, $value["CMS_SLUG"], $label, $key);
            }
        }

        foreach ($params["commentaires"]["structures"] as $nomSection => $sections) {
            foreach ($sections as $key => $value) {
                $params["commentaires"]["structures"][$nomSection][$key]["commentaires"] = $this->commentaireService->getCommentaireLibreByStructure($value["CMS_ID"]);
            }
        }

        return $this->render('Modules/Commentaire/index.html.twig', $params);
    }

    public function previsualisation(Request $request, array $params = [], $export = false)
    {
        $params["export"] = true;

        set_time_limit(0);

        $params["cabinetComptable"] = $this->stationService->getCabinetComptableByStation();

        foreach ($this->commentaireService->getAllCommentairesStructure() as $key => $value) {
            $label = substr($value["CMS_ORDRE"], 0, 1) . ". " . $value["CMS_ONGLET"];
            $params["commentaires"]["structures"][$label][$key] = $value;

            if($value["CMS_SLUG"]) {
                $this->setSpecificityByContent($params, $value["CMS_SLUG"], $label, $key);
            }
        }

        foreach ($params["commentaires"]["structures"] as $nomSection => $sections) {
            foreach ($sections as $key => $value) {
                if (empty($content = $this->commentaireService->getCommentaireLibreByStructure($value["CMS_ID"]))) {
                    continue;
                }

                $params["commentaires"]["structures"][$nomSection][$key]["commentaires"] = $content;
            }
        }

        if($export) {
            $params = $this->cleanUpEmptySection($params);
            
            return $this->getContent('Modules/Commentaire/export.html.twig', $params);
        }

        return $this->render('Modules/Commentaire/index.html.twig', $params);
    }

    private function cleanUpEmptySection(array $datas): array
    {
        foreach ($datas['commentaires']['structures'] as $sectionTitle => $sectionData) {
            foreach ($sectionData as $id => $data) {
                if (isset($data['contents']) || isset($data['commentaires'])) {
                    continue;
                }

                unset($datas['commentaires']['structures'][$sectionTitle][$id]);
            }

            if (empty($datas['commentaires']['structures'][$sectionTitle])) {
                unset($datas['commentaires']['structures'][$sectionTitle]);
            }
        }

        return $datas;
    }

    public function export(Request $request, array $params = [])
    {
        set_time_limit(0);
        $mpdf = new Mpdf(["tempDir" => "/tmp"]);
        // Si on souhaite avoir un débug dans les logs de la gestion des images
        $mpdf->showImageErrors = true;

        $html = mb_convert_encoding($this->previsualisation($request, $params, true), 'UTF-8', 'UTF-8');

        $mpdf->WriteHTML($html);
        $mpdf->Output();

        // TODO check usage
        return $this->render('Modules/Commentaire/export.html.twig', $params);
    }

    public function setSpecificityByContent(&$params, $slug, $label, $key) {
        $specificities = $this->commentaireService->getSpecificities($slug);

        if(isset($specificities["type"]) && $specificities["type"]) {
            $params["commentaires"]["structures"][$label][$key]["type"] = $specificities["type"];
        }
        if(isset($specificities["function"]) && $specificities["function"]) {
            $params["commentaires"]["structures"][$label][$key]["contents"] = $specificities["function"];
        }
        if(isset($specificities["commentaires"]) && $specificities["commentaires"]) {
            $params["commentaires"]["structures"][$label][$key]["sousCommentaires"] = $specificities["commentaires"];
        }
        if(isset($specificities["typeCommentaires"]) && $specificities["typeCommentaires"]) {
            $params["commentaires"]["structures"][$label][$key]["typeCommentaires"] = $specificities["typeCommentaires"];
        }
    }

    public function getCommentaire(Request $request,  array $params = [])
    {
        $detail = $this->commentaireService->getDetailCommentaireByPoste($request->getParams()['code'], $request->getParams()['type']);

        return $this->render('Modules/Commentaire/modal.html.twig', ["detail" => $detail]);
    }
    
    public function newCommentaire(Request $request)
    {
        $id = $this->commentaireService->insertCommentaire($request->getParams());
        $this->session->setFlash('success', 'Commentaire ajouté.');

        return $this->responseJson(["id" => $id, "action" => "new"]);
    }

    public function newCommentaireLibre(Request $request)
    {
        $id = $this->commentaireService->insertCommentaireLibre($request->getParams());
        $this->session->setFlash('success', 'Commentaire ajouté.');

        return $this->responseJson(["id" => $id, "action" => "new"]);
    }

    public function updateCommentaire(Request $request, array $params = [])
    {
        $content = $request->getParams()["CMT_COMMENTAIRE"] ?? '';
        $id = $params["id"];

        if(strip_tags($content) == "" || strip_tags($content) == " "){
            $this->commentaireService->deleteCommentaire($params["id"]);
            $id = "new";
            $action = "delete";
            $this->session->setFlash('success', 'Commentaire supprimé.');
        } else {
            $this->commentaireService->updateCommentaire($params["id"], $request->getParams());
            $action = "update";
            $this->session->setFlash('success', 'Commentaire modifié.');
        }

        return $this->responseJson(["id" => $id, "action" => $action]);
    }

    public function updateCommentaireLibre(Request $request, array $params = [])
    {
        $content = $request->getParams()["CML_COMMENTAIRE"] ?? '';
        $id = $params["id"];

        if(strip_tags($content) == ""){
            $this->commentaireService->deleteCommentaireLibre($params["id"]);
            $id = "new";
            $action = "delete";
            $this->session->setFlash('success', 'Commentaire supprimé.');
        } else {
            $this->commentaireService->updateCommentaireLibre($params["id"], $request->getParams());
            $action = "update";
            $this->session->setFlash('success', 'Commentaire modifié.');
        }

        return $this->responseJson(["id" => $id, "action" => $action]);
    }

    public function deleteCommentaire(Request $request, array $params = [])
    {
        if(isset($params["id"]) && $params["id"]) {
            $this->commentaireService->deleteCommentaire($params["id"]);
            $this->session->setFlash('success', 'Commentaire supprimé.');
        }

        return $this->responseJson(["id" =>$params["id"]]);
    }

    public function deleteCommentaireLibre(Request $request, array $params = [])
    {
        if(isset($params["id"]) && $params["id"]) {
            $this->commentaireService->deleteCommentaireLibre($params["id"]);
            $this->session->setFlash('success', 'Commentaire supprimé.');
        }

        return $this->responseJson(["id" =>$params["id"]]);
    }

    public function getCommentaireLibre(Request $request, array $params = [])
    {
        $detail = $this->commentaireService->getCommentaireLibre($params["id"]);

        return $this->render('Modules/Commentaire/modal.html.twig', ["detail" => $detail]);
    }
}
