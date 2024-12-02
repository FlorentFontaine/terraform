<?php

namespace Facades\Modules\Commentaire;

use Facades\Facade;
use Services\Modules\Commentaire\CommentaireService;

class Commentaire extends Facade
{
    public static function definition(): string
    {
        return CommentaireService::class;
    }
}
