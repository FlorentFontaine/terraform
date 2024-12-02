<?php

namespace Traits\Modules\Commentaire;

use Repositories\Modules\Commentaire\CommentaireTableRepository;

trait CommentaireTableTrait
{

    public function actionOnBilan($data)
    {
        return $this->action($data, 'bilan');
    }

    public function actionOnPoste($data)
    {
        return $this->action($data, 'poste');
    }

    public function actionOnCompte($data)
    {
        return $this->action($data, 'compte');
    }

    public function getCommentaire($cmtId)
    {
        return (new CommentaireTableRepository())->getCommentaire($cmtId);
    }

    public function getCommentaireByType($id, $type, $fetchAll = false)
    {
        return (new CommentaireTableRepository())->getCommentaireByType($id, $type, $fetchAll);
    }

    public function getDetailCommentaireByPoste($id, $type): array
    {
        $methodName = 'getDetailCommentaire' . ucfirst($type);
        return $this->$methodName($id);
    }

    public function getDetailCommentaireByPosteBySlug($slug, $type): array
    {
        $methodName = 'getDetailCommentaire' . ucfirst($type) . 'BySlug';
        return $this->$methodName($slug);
    }

    public function getDetailCommentaireBilan($id): array
    {
        $poste = $this->bilanRepository->getPostesBilan($id);
        $mesComptes = $this->bilanRepository->getComptesBilan($poste["CPTB_NUM"], $poste["CPTB_SFAMILLE"]);

        return $this->formatCommentaire($id, $poste, $mesComptes, 'bilan', $poste["CPTB_SFAMILLE"]);
    }

    public function getDetailCommentairePoste($id): array
    {

        $poste = $this->posteRepository->getPoste($id);
        $type = isset($poste["Type"]) && $poste["Type"] == "Produits" ? "vente" : null;
        $mesComptes = $this->detailPosteRepository->getDetailCompteByPoste($id, $type);

        return $this->formatCommentaire($id, $poste, $mesComptes, 'poste');
    }

    public function getDetailCommentaireBilanBySlug($slug): array
    {
        $postes = $this->bilanRepository->getPostesBilanBySlug($slug);

        $mesCommentaires = [];

        foreach ($postes as $poste) {
            $mesComptes = $this->bilanRepository->getComptesBilan($poste["CPTB_NUM"], $poste["CPTB_SFAMILLE"]);
            $mesCommentaires[] = $this->formatCommentaire($poste["CPTB_NUM"], $poste, $mesComptes, 'bilan', $poste["CPTB_SFAMILLE"]);
        }

        return $mesCommentaires;
    }

    public function getDetailCommentairePosteBySlug($slug): array
    {

        $postes = $this->posteRepository->getPosteBySousFamille($slug);

        $mesCommentaires = [];

        foreach ($postes as $poste) {
            $mesComptes = $this->detailPosteRepository->getDetailCompteByPoste($poste["codePoste"]);
            $mesCommentaires[] = $this->formatCommentaire($poste["codePoste"], $poste, $mesComptes, 'poste');
        }

        return $mesCommentaires;
    }

    public function getCommentaires(): array
    {
        return (new CommentaireTableRepository())->getCommentaires();
    }

    public function insertCommentaire($data)
    {
        $data["CMT_MOIS"] = $_SESSION["MoisHisto"];
        $data["DOS_NUM"] = $_SESSION["station_DOS_NUM"];
        return (new CommentaireTableRepository())->insertCommentaire($data);
    }

    public function updateCommentaire($id, $data)
    {
        return (new CommentaireTableRepository())->updateCommentaire($id, $data);
    }

    public function deleteCommentaire($id)
    {
        return (new CommentaireTableRepository())->deleteCommentaire($id);
    }

    public function action($data, $type)
    {
        // Si le module n'est pas actif ou si le dossier est validé, on ne fait rien
        if (!$this->isModuleEnable() || $_SESSION['NbAno'] === 0) {
            return $data["value"];
        }

        $commentaires = $this->getDetailCommentaireByPoste($data["key"], $type);

        $dataId = $commentaires["poste"]["commentaire"] ? $commentaires["poste"]["commentaire"]["CMT_ID"] : "new";
        $dataChildCommented = "no";

        foreach ($commentaires["comptes"] as $compte) {
            if ($compte["commentaire"]) {
                $dataChildCommented = "yes";
                break;
            }
        }

        return "<span class='commentaires' data-child-commented='" . $dataChildCommented . "' data-id='" . $dataId . "' data-type='" . $type . "' data-code='" . $data["key"] . "'>". $data["value"] ."</span>";
    }

    public function formatCommentaire($id, $poste, $comptes, $type, $ssFamille = null): array
    {
        $poste["commentaire"] = $this->getCommentaireByType($id, $type);
        $poste["libelle"] = $poste["CPTB_LIB"] ?? $poste["Libelle"];
        $poste["id"] = $poste["CPTB_NUM"] ?? $poste["codePoste"];
        $poste["type"] = $type;

        $totalPoste = 0;

        $commentaires = $this->getCommentaireByType(array_keys($comptes), 'compte', true);
        $resultatComptes = $this->balanceRepository->getResultatsCompte();
        $stockRetenuBilanParCompte = $this->bilanRepository->getStockRetenuByCompte();

        foreach ($comptes as $codeCompte => $compte) {
            $comptes[$codeCompte]["commentaire"] = $commentaires[$codeCompte] ?? [];

            if($ssFamille == "Stock") {
                $resultat = $stockRetenuBilanParCompte[$codeCompte]["somme"] ?? 0;
            } else {
                $resultat = $resultatComptes[$codeCompte]["BAL_CUMUL"] ?? 0;
            }

            $comptes[$codeCompte]["resultat"] = $resultat;
            $comptes[$codeCompte]["id"] = $codeCompte;
            $comptes[$codeCompte]["type"] = 'compte';

            $totalPoste += $resultat;
        }

        $poste["resultat"] = $totalPoste;

        return [
            "poste" => $poste,
            "comptes" => $comptes
        ];
    }
}
