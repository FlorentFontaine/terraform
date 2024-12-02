<?php

namespace Services;

use Repositories\StationRepository;
use Services\AbstractService;

class StationService extends AbstractService
{
    private StationRepository $stationRepository;

    public function __construct(StationRepository $stationRepository) {
        $this->stationRepository = $stationRepository;
    }

    public function getAllExerciceByStation(): array
    {
        return $this->stationRepository->getAllExerciceByStation();
    }

    public function getCabinetComptableByStation(): array
    {
        return $this->stationRepository->getCabinetComptableByStation();
    }

    public function getCabinetJuridiqueByStation(): array
    {
        return $this->stationRepository->getCabinetJuridiqueByStation();
    }
}
