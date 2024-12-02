<?php

namespace Repositories\Modules\Commentaire;

class CommentaireRepository
{
    protected function whereDosNumOnPeriod($query, $field)
    {
        return $this->whereDosNum($query)
            ->where($field . ' = :periode')
            ->setParam('periode', $_SESSION["MoisHisto"]);
    }

    protected function whereDosNum($query)
    {
        return $query->where('DOS_NUM = :dosNum')
            ->setParam('dosNum', $_SESSION['station_DOS_NUM']);
    }

    protected function getFieldNameByType($type) : string
    {
        $field = '';
        switch ($type) {
            case 'bilan':
                $field = 'CPB_NUM';
                break;
            case 'compte':
                $field = 'code_compte';
                break;
            case 'poste':
                $field = 'codePoste';
                break;
            default:
                break;
        }

        return $field;
    }
}
