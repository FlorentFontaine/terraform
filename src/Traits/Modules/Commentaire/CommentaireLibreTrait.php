<?php

namespace Traits\Modules\Commentaire;

use Repositories\Modules\Commentaire\CommentaireLibreRepository;

trait CommentaireLibreTrait
{

    public function getCommentaireLibre($id): array
    {
        return (new CommentaireLibreRepository())->getCommentaire($id);
    }

    public function getCommentaireLibreByStructure($id): array
    {
        return (new CommentaireLibreRepository())->getCommentaireByStructure($id);
    }

    public function insertCommentaireLibre($data)
    {
        $data["CML_MOIS"] = $_SESSION["MoisHisto"];
        $data["DOS_NUM"] = $_SESSION["station_DOS_NUM"];

        return (new CommentaireLibreRepository())->insertCommentaire($data);
    }
    
    public function updateCommentaireLibre($id, $data)
    {
        return (new CommentaireLibreRepository())->updateCommentaire($id, $data);
    }

    public function deleteCommentaireLibre($id)
    {
        return (new CommentaireLibreRepository())->deleteCommentaire($id);
    }
}
