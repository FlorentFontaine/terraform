<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../ctrl/ctrl.php';
include_once __DIR__ . '/../dbClasses/station.php';
include_once __DIR__ . '/../dbClasses/comptable.php';
require_once __DIR__ . '/../htmlClasses/table.php';

$Tab = CRP::get_TabCRP();

include __DIR__ . "/GestionCRP.liste.vue.php";
