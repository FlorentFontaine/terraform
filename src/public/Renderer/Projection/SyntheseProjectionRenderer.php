<?php

namespace App\Renderer\Projection;

use synthese;

require_once __DIR__ . '/../../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../../synthese/synthese.class.php';

class SyntheseProjectionRenderer
{
    public function render(): array
    {
        $MoisVoulu = $_SESSION["MoisHisto"];
        $Type = 'Produits';
        $LignesProduits = synthese::getTabProjection($Type,$MoisVoulu,false,false,false,[]);
        $Type = 'Charges';
        $LignesCharges = synthese::getTabProjection($Type,$MoisVoulu,false,false,false,[]);

        return [$LignesProduits, $LignesCharges];
    }
}
