<?php

use App\Renderer\Projection\SyntheseProjectionRenderer;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../Renderer/Projection/SyntheseProjectionRenderer.php';

list($LignesProduits, $LignesCharges) = (new SyntheseProjectionRenderer())->render();

include_once __DIR__ . '/../../Templates/Projection/SyntheseProjectionTemplate.php';
