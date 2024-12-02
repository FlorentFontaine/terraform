<?php

namespace Services;

use Repositories\DossierRepository;
use Services\AbstractService;

class DossierService extends AbstractService
{
    private DossierRepository $dossierRepository;

    public function __construct(DossierRepository $dossierRepository) {
        $this->dossierRepository = $dossierRepository;
    }

    public function getBalanceImportByDossier(): array
    {
        return $this->dossierRepository->getBalanceImportByDossier();
    }
}
