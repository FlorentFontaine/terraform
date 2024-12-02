<?php
use Helpers\StringHelper;

class Export
{
    public static function station()
    {
        echo "flag_station\r\n";
        $MonTab = StringHelper::cleanTab("station_STA", $_SESSION, "station_STA_MDP");

        foreach ($MonTab as $UneCle => $UneInfo) {
            echo "$UneCle\t$UneInfo\r\n";
        }
    }

    public static function prev($Type)
    {
        echo "flag_prev_$Type\r\n";
        require_once __DIR__ .'/../PrevBack/prev.class.php';

        $MonTab = Previsionnel::getTab($Type, $_SESSION["MoisHisto"]);

        self::write(self::format($MonTab));
    }

    public static function marge()
    {
        echo "flag_marge\r\n";
        require_once __DIR__ .'/../MargeBack/marge.class.php';

        $MonTab = Marge::getTab($_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function charge()
    {
        echo "flag_charges\r\n";
        require_once __DIR__ .'/../compChargesBack/compCharges.class.php';

        $MonTab = compChargesProd::getTab("Charges", $_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function produit()
    {
        echo "flag_produits\r\n";
        require_once __DIR__ .'/../compChargesBack/compCharges.class.php';

        $MonTab = compChargesProd::getTab("Produits", $_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function rensTaux()
    {
        echo "flag_taux\r\n";
        require_once __DIR__ .'/../RenseignementBack/Renseignement.class.php';

        $MonTab = ListeRenseignement::getTab($_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function carb()
    {
        echo "flag_carb\r\n";
        require_once __DIR__ .'/../RenseignementBack/Renseignement.class.php';

        $MonTab = ListeRenseignement::getTabCarburant($_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function divers()
    {
        echo "flag_divers\r\n";
        require_once __DIR__ .'/../RenseignementBack/Renseignement.class.php';

        $MonTab = ListeRenseignement::getTabDivers($_SESSION["MoisHisto"]);
        self::write(self::format($MonTab));
    }

    public static function cle()
    {
        echo "flag_cle\r\n";
        require_once __DIR__ .'/../RenseignementBack/Renseignement.class.php';

        $MonTab = ListeRenseignement::getTabSaison(null, $MesSum);

        self::write(self::format($MonTab));
    }

    public static function getVal(string $key)
    {
        $key = str_replace("&nbsp;", "", $key);

        if (stripos($key, 'input') !== false) {
            $xml = simplexml_load_string("<monxml>" . $key . "</monxml>");

            foreach ($xml->input as $cle) {
                if ((string)$cle['type'] == 'text') {
                    return (string)$cle['value'];
                }
            }

            // Si des balises input à côté de la valeur, mettre la valeur entre les balise <valeur></valeur>
            foreach ($xml->value as $in) {
                return (string)$in;
            }
        }

        return $key;
    }

    private static function write(array $TabFormate)
    {
        foreach ($TabFormate as $UneLigne) {
            echo implode("\t", $UneLigne) . "\r\n";
        }
    }

    private static function format($MonTab): array
    {
        $MesLigneExp = [];

        foreach ($MonTab as $Cel) {
            $UneLigneExp = [];

            foreach ($Cel as $Val) {
                if (is_array($Val)) {
                    foreach ($Val as $cleVal => $Param) {
                        $UneLigneExp[] = htmlspecialchars_decode(strip_tags(str_replace("&nbsp;", "", self::getVal($cleVal))));
                    }
                } else {
                    $UneLigneExp[] = htmlspecialchars_decode(strip_tags(str_replace("&nbsp;", "", self::getVal($Val))));
                }
            }

            if (!empty($UneLigneExp) && $UneLigneExp[0] != "") {
                $MesLigneExp[] = $UneLigneExp;
            }
        }

        return $MesLigneExp;
    }
}
