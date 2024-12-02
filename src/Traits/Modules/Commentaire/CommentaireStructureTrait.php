<?php

namespace Traits\Modules\Commentaire;

use Repositories\Modules\Commentaire\CommentaireStructureRepository;

trait CommentaireStructureTrait
{

    public function getAllCommentairesStructure(): array
    {
        return (new CommentaireStructureRepository())->getAllCommentairesStructure();
    }

    public function getCommentairesStructureBySection($section): array
    {
        return (new CommentaireStructureRepository())->getCommentaireStructure($section);
    }
}
