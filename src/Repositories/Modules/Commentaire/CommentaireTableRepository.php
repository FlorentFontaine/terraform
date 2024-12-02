<?php

namespace Repositories\Modules\Commentaire;

use Classes\DB\QueryBuilder;

class CommentaireTableRepository extends CommentaireRepository
{
    public function getCommentaire($cmtId)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_tableau')
            ->where('CMT_ID = :cmtId')
            ->setParam('cmtId', $cmtId);

        $query = $this->whereDosNumOnPeriod($query, "CMT_MOIS");

        return $query->get();
    }

    public function getCommentaireByType($id, $type, $fetchAll = false)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_tableau')
            ->where('CMT_TYPE = :TYPE')
            ->setParam('TYPE', $type);

        if(is_array($id)) {
            if (empty($id)) {
                return [];
            }

            $query->where($this->getFieldNameByType($type) . ' IN (' . implode(",", $id) . ')');
        } else {
            $query->where($this->getFieldNameByType($type) . ' = :ID')
                ->setParam('ID', $id);
        }

        $query = $this->whereDosNumOnPeriod($query, "CMT_MOIS");

        if ($fetchAll) {
            $query->index('commentaires_tableau.code_compte');

            return $query->getAll();
        }

        return $query->get();
    }

    public function getCommentairesByType($type)
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_tableau')
            ->where('CMT_TYPE = :TYPE')
            ->setParam('TYPE', $type);

        $query = $this->whereDosNumOnPeriod($query, "CMT_MOIS");

        return $query->getAll();
    }

    public function getCommentaires()
    {
        $query = new QueryBuilder();
        $query->select('*')
            ->from('commentaires_tableau');

        $query = $this->whereDosNumOnPeriod($query, "CMT_MOIS");

        return $query->getAll();
    }

    public function insertCommentaire($data)
    {
        $query = new QueryBuilder();
        return $query->insert('commentaires_tableau', $data);
    }

    public function updateCommentaire($id, $data)
    {
        $query = new QueryBuilder();
        return $query->update('commentaires_tableau', $data, ['CMT_ID' => $id]);
    }

    public function deleteCommentaire($id)
    {
        $query = new QueryBuilder();
        return $query->delete('commentaires_tableau', ['CMT_ID' => $id]);
    }
}
