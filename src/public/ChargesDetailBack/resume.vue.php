<?php use htmlClasses\TableV2;

if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
    <head>
        <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
        <link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">
<title>
            <?php
            if (isset($Produits) && $Produits == '1') {
                echo "D&eacute;tail des comptes produits";
            }

            if (isset($Type) && $Type == "Charges") {
                echo "D&eacute;tail des comptes charges";
            }
            ?>
        </title>
    </head>

    <body>

    <?php
    include_once __DIR__ . "/../include/entete.inc.php";
}//entetepied
?>

    <center>
        <table width="80%">
            <tr>
                <td>
                    <table align="center" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000">
                        <tbody>
                        <?php
                        $NbCols = 2;

                        if (!$MoisVoulu) {
                            $NbCols--;
                        }

                        foreach ($MesLignes as $codecompte => $UneLigne) {
                            $cssligne1 = "";

                            if (stristr($codecompte, "STOTAL") || stristr($codecompte, "Poste")) {
                                $cssligne1 = "bolder";
                            }
                            elseif (stristr($codecompte, "ENCADRE")) {
                                $cssligne1 = "EnteteTab";
                            }

                            $cssli = array("class" => $cssligne1);

                            echo table::getLine($UneLigne, $cssli, $NbCols);
                        } ?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </center>

<?php if (!isset($EntetePiedFalse) || !$EntetePiedFalse) {
    include_once __DIR__ . "/../include/pied.inc.php";
?>
    </body>
    </html>
    <?php
}
