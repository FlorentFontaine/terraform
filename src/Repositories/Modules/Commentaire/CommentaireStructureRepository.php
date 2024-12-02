<?php

namespace Repositories\Modules\Commentaire;

use Classes\DB\QueryBuilder;

class CommentaireStructureRepository extends CommentaireRepository
{
    public function getAllCommentairesStructure()
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_structure')
            ->orderBy('CMS_ORDRE');

        return $query->getAll();
    }

    public function getCommentaireStructure($section)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_structure')
            ->where('CMS_ONGLET = :section')
            ->setParam('section', $section)
            ->orderBy('CMS_ORDRE');

        return $query->getAll();
    }
}
