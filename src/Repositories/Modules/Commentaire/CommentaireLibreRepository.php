<?php

namespace Repositories\Modules\Commentaire;

use Classes\DB\QueryBuilder;

class CommentaireLibreRepository extends CommentaireRepository
{
    public function getCommentaire($id)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_libre')
            ->where('CML_ID = :id')
            ->setParam('id', $id);

        return $query->get() ?? [];
    }

    public function getCommentaireByStructure($id)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_libre')
            ->where('CMS_ID = :id')
            ->setParam('id', $id);

        $query = $this->whereDosNumOnPeriod($query, "CML_MOIS");

        return $query->getAll();
    }

    public function insertCommentaire($data)
    {
        $query = new QueryBuilder();
        return $query->insert('commentaires_libre', $data);
    }

    public function updateCommentaire($id, $data)
    {
        $query = new QueryBuilder();
        return $query->update('commentaires_libre', $data, ['CML_ID' => $id]);
    }

    public function deleteCommentaire($id)
    {
        $query = new QueryBuilder();
        return $query->delete('commentaires_libre', ['CML_ID' => $id]);
    }
}
