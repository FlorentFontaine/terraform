<?php

use Controller\BilanController;
use Controller\DetailPosteController;
use Controller\HomeController;
use Controller\LieuController;
use Controller\Modules\Commentaire\CommentaireController;

return [
    ['GET', '/', [HomeController::class, 'home']],
    ['GET', '/flash', [HomeController::class, 'flash']],

    // PDV
    ['GET', '/pdv', [LieuController::class, 'index']],

    // Bilan
    ['GET', '/bilan-test', [BilanController::class, 'index']],

    // Détail poste
    ['POST', '/detail/{type:\S+}/poste/{id:\d+}', [DetailPosteController::class, 'show']],

    // --------------
    //     Modules
    // --------------

    // --- Commentaire
        // --- Libre
        ['GET', '/commentaire/section/{section:\S+}/show/new', [CommentaireController::class, 'getCommentaireLibre']],
        ['GET', '/commentaire/section/{section:\S+}/show/{id:\d+}', [CommentaireController::class, 'getCommentaireLibre']],
        ['POST', '/commentaire/section/{section:\S+}/show/new', [CommentaireController::class, 'newCommentaireLibre']],
        ['POST', '/commentaire/section/{section:\S+}/show/{id:\d+}', [CommentaireController::class, 'updateCommentaireLibre']],
        ['DELETE', '/commentaire/section/{section:\S+}/show/{id:\d+}', [CommentaireController::class, 'deleteCommentaireLibre']],
        ['GET', '/commentaire/section/previsualisation', [CommentaireController::class, 'previsualisation']],
        ['GET', '/commentaire/section/{section:\S+}', [CommentaireController::class, 'index']],
        ['GET', '/commentaire/export', [CommentaireController::class, 'export']],
    
        // --- Table
        ['GET', '/commentaire/new', [CommentaireController::class, 'getCommentaire']],
        ['GET', '/commentaire/{id:\d+}', [CommentaireController::class, 'getCommentaire']],
        ['POST', '/commentaire/new', [CommentaireController::class, 'newCommentaire']],
        ['POST', '/commentaire/{id:\d+}', [CommentaireController::class, 'updateCommentaire']],
        ['DELETE', '/commentaire/{id:\d+}', [CommentaireController::class, 'deleteCommentaire']],
];
