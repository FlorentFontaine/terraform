<?php

use Classes\DB\Database;
use Guzzle\Http\EntityBody;
use Aws\S3\Exception\S3Exception;

require_once __DIR__ . '/../dbClasses/AccesDonnees.php';

class gardeBack
{
    public static $Seuil_Agios = 100;
    public static $Seuil_SoldeCaisse = 150;

    /**
     * VA= Valeur Arrivée
     * VD= Valeur Départ
     * DESC = true ou false - à true signifie que la baisse est positive
     */
    static function getTendance($d = NULL)
    {
        $image = '';

        if (!isset($d['VA']) || !isset($d['VD'])) {
            return $image;
        }

        if ($d['VA'] > $d['VD']) {
            //VA > VD négatif = fleche rouge haut
            if (isset($d['DESC']) && $d['DESC']) {
                $image = '<img src="../images/flecherouge.png" style="width: 12px" alt="flèche rouge" />';
            } else {
                $image = '<img src="../images/flecheverte-haut.png" style="width: 12px" alt="flèche verte vers le haut" />';
            }
        } elseif ($d['VD'] > $d['VA']) {
            //VA < VD négatif = fleche verte bas
            if (isset($d['DESC']) && $d['DESC']) {
                $image = '<img src="../images/flecheverte.png" style="width: 12px" alt="flèche verte" />';
            } else {
                $image = '<img src="../images/flecherouge-bas.png" style="width: 12px" alt="flèche rouge vers le bas" />';
            }
        } elseif ($d['VD'] == $d['VA']) {
            $image = '<img src="../images/flechejaune.png" style="width: 12px" alt="flèche jaune" />';
        }

        return $image;
    }

    static function saveFileCom($file, $dosNum, $MoisVoulu)
    {
        global $s3;
        //si le fichier a bien �t� uploader
        if (is_uploaded_file($file["tmp_name"])) {
            try {
                $lnBalanceImport = db_gardeBack::getBalImportM1($dosNum, $MoisVoulu);
                $path = $lnBalanceImport[0]["BALI_FILECOM"];

                if ($path) {
                    self::delFileCom($dosNum, $MoisVoulu);
                }

                $path = getenv("APP_S3_PREFIX") . "/commentaires/";
                $nom = md5(uniqid(rand(), true));
                $extension_upload = strtolower(strrchr($file['name'], '.'));
                $path .= $nom . $extension_upload;

                $s3->putObject(array(
                    'Bucket' => getenv("APP_S3_BUCKET"),
                    'Key' => $path,
                    'ContentType' => mime_content_type($file["tmp_name"]),
                    'Body' => EntityBody::factory(fopen($file["tmp_name"], 'r+'))
                ));

                db_gardeBack::setComImport($dosNum, $MoisVoulu, null, $path);
            } catch (Exception $e) {
                return false;
            }
        }
    }

    static function downLoadCom($dosNum, $MoisVoulu)
    {
        $lnBalanceImport = db_gardeBack::getBalImportM1($dosNum, $MoisVoulu);
        $path = $lnBalanceImport[0]["BALI_FILECOM"];

        gardeBack::downloadCommentFile($path);
    }


    static function downloadCommentFile($path)
    {
        global $s3;
        /** @var array $Erreur array('libelle'=>string, 'desc'=>string, 'data'=>...) * */
        $Erreur['libelle'] = 'Probleme de recuperation de fichier';
        $Erreur['desc'] = 'Le fichier ne peut être récupéré : <br />';

        //check object exist
        try {
            $fileHeaders = $s3->headObject(array(
                'Bucket' => getenv("APP_S3_BUCKET"),
                'Key' => $path,
            ));
        } catch (S3Exception $e) {
            $Erreur['data'] = 'Fichier introuvable';
            return $Erreur;
        }

        //getObject
        try {
            $s3Object = $s3->getObject(array(
                'Bucket' => getenv("APP_S3_BUCKET"),
                'Key' => $path
            ));
        } catch (S3Exception $e) {
            $Erreur['data'] = 'Fichier introuvable';
            return $Erreur;
        }


        /** Recup. d'info sur le fichier    **/
        $filename = basename($path);
        $file_extension = strtolower(substr(strrchr($filename, "."), 1));

        header('Content-Type: ' . $fileHeaders["ContentType"]);
        header('Content-Disposition: attachment; filename=' . $filename);//On utilise le nom de fichier avec les espaces remplacés par des underscores
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $fileHeaders["ContentLength"]);
        ob_clean();
        flush();
        echo $s3Object['Body'];
        die();
    }

    static function delFileCom($dosNum, $MoisVoulu)
    {
        global $s3;
        // On récupère la ligne correspondante dans balance_Import
        $lnBalanceImport = db_gardeBack::getBalImportM1($dosNum, $MoisVoulu);
        // On récupère le lien vers le fichier
        $pathFileCom = $lnBalanceImport[0]["BALI_FILECOM"];

        //on supprimer le fichier
        $s3->deleteObject(array(
            'Bucket' => getenv("APP_S3_BUCKET"),
            'Key' => $pathFileCom
        ));

        //on update la table
        db_gardeBack::setComImport($dosNum, $MoisVoulu, null, "del");//"del permet d'indiquer ds la fonction qu'il faut supprimer le chemin du fichier"
    }

    static function cabAuthToDepFileCom($cabNum): bool
    {
        $tabCabAuthToDepFileCom = db_gardeBack::getCabAuthToSendCom();

        return in_array($cabNum, $tabCabAuthToDepFileCom);
    }
}

class db_gardeBack extends dbAcces
{
    static function getCabAuthToSendCom(): array
    {
        $query = "SELECT CAB_NUM FROM cabinet WHERE CAB_UPLOADCOM ='1'";

        Database::query($query);
        $tabCab = array();

        while ($ln = Database::fetchArray()) {
            $tabCab[] = $ln["CAB_NUM"];
        }

        return $tabCab;
    }
}
