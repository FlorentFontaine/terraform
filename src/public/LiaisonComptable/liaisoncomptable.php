<?php

$notselect = true;

use Classes\DB\Database;

include_once '../ctrl/ctrl.php';


if ($valide) {
    $LICO_nouvcompte = trim($LICO_nouvcompte);
    if (!is_numeric($LICO_nouvcompte)) {
        $error = 1;
    } /*elseif(strlen($LICO_nouvcompte)!=7)
	{

		$error = 2;

	}*/
    elseif (!$error) {
        if ($LICO_NUM) {
            $requpd = 'UPDATE liaisoncompte SET LICO_code_compte ="' . $LICO_code_compte . '", LICO_nouvcompte="' . $LICO_nouvcompte . '",STA_NUM = "' . $STA_NUM . '" WHERE LICO_NUM ="' . $LICO_NUM . '" ';
            Database::query($requpd);
        } else {
            $reqinsert = 'INSERT INTO liaisoncompte (CAB_NUM,LICO_code_compte,LICO_nouvcompte,STA_NUM) VALUE ("' . $User->Var["CAB_NUM"] . '","' . $LICO_code_compte . '","' . $LICO_nouvcompte . '","' . $STA_NUM . '");';
            Database::query($reqinsert);
        }
        header("Location:../LiaisonComptable/liaisoncomptable.php");
    }
} elseif ($supp) {
    $reqsupp = "DELETE FROM liaisoncompte WHERE LICO_NUM ='$LICO_NUM'";
    Database::query($reqsupp);
    $_GET['LICO_NUM'] = false;
    $LICO_nouvcompte = "";
} elseif ($_GET['LICO_NUM']) {
    $req = "SELECT * FROM liaisoncompte WHERE LICO_NUM ='" . $_GET['LICO_NUM'] . "' ";
    $result = Database::query($req);
    $ligneresult = Database::fetchArray($result);
    $LICO_code_compte = $ligneresult['LICO_code_compte'];
    $LICO_nouvcompte = $ligneresult['LICO_nouvcompte'];
    $STA_NUM = $ligneresult['STA_NUM'];
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="../print.css" type="text/css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <title>Liaison comptable</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">

    <style type="text/css">

        .tabBalance .EnteteTab td {
            text-align: center;
        }


    </style>


</head>

<body>

<?php
include("../include/entete.inc.php");

?>
<center>
    <!--<div class="titresection">Equivalence des comptes</div>-->
</center>
<div align="center">
    <table style="width: 100%; margin: -1px;">
        <tr>
            <td class="EnteteTab TitreTable" style="text-align:center;font-weight:bold;border:none">
                EQUIVALENCE DES COMPTES
            </td>
        </tr>
    </table>
    <form method='post'>
        <table style='border:1px solid black;margin:1%;background-color:#E6E6E6;width:600px; margin-bottom: 24px; padding: 8px;'>
            <tr>
                <td>Compte inconnu</td>
                <td>
                    <input name='LICO_nouvcompte' id='LICO_nouvcompte' style="width: 400px" value='<?php
                    if ($LICO_nouvcompte)
                        echo $LICO_nouvcompte;
                    ?>'/>
                    <?php if ($_GET['LICO_NUM']) {
                        echo "<input type='hidden' name='LICO_NUM' id='LICO_NUM' value='" . $_GET['LICO_NUM'] . "'>";
                    }
                    ?>

                </td>
            </tr>

            <tr>
                <td>Nouveau compte</td>
                <td>
                    <select name='LICO_code_compte' id='LICO_code_compte' style="width: 100%">
                        <?php
                        $req = "SELECT * FROM comptes order by numero";
                        $result = Database::query($req);
                        while ($ligneresult = Database::fetchArray($result)) {
                            echo "<option value='" . $ligneresult['code_compte'] . "'";

                            if ($ligneresult['code_compte'] == $LICO_code_compte)
                                echo " selected ";

                            echo ">" . $ligneresult['numero'] . " " . utf8_encode($ligneresult['libelle']) . "</option>";
                        }

                        ?>
                    </select>

                </td>
            </tr>

            <tr>
                <td>Soci&eacute;t&eacute;</td>
                <td>
                    <?php


                    $req = "SELECT distinct station.STA_NUM,STA_SARL FROM station join stationcc on stationcc.STA_NUM = station.STA_NUM join comptable on comptable.CC_NUM = stationcc.CC_NUM join cabinet on cabinet.CAB_NUM = comptable.CAB_NUM where 1 and cabinet.CAB_NUM = '" . $User->Var["CAB_NUM"] . "' order by 2";

                    ?>
                    <select name='STA_NUM' id='STA_NUM' style="width: 100%">
                        <option value="0">Toutes</option>
                        <?php

                        $result = Database::query($req);
                        while ($ligneresult = Database::fetchArray($result)) {
                            echo "<option value='" . $ligneresult['STA_NUM'] . "'";

                            if ($ligneresult['STA_NUM'] == $STA_NUM)
                                echo " selected='selected' ";

                            echo ">" . $ligneresult['STA_SARL'] . "</option>";
                        }

                        ?>
                    </select>

                </td>

                <td><input type='submit' value='Enregistrer' name='valide' id='valide' style='width:90px'/></td>
            </tr>
            <?php if ($_GET['LICO_NUM']) {
                echo "<tr><td colspan='2'></td><td><input type='submit' name='supp' id='supp' value='Supprimer'  style='width:90px'></td></tr>";
            }
            ?>


        </table>
    </form>

    <table style='margin:1%;width:600px;border:1px solid black;' class="tabBalance" align="center"
           bordercolordark="#000000" bordercolorlight="#000000">
        <tr>
            <td class='EnteteTab' style='width:90px;'>Compte inconnu</td>
            <td class='EnteteTab' style='width:200px;'>Nouveau compte</td>
            <td class='EnteteTab'>Soci&eacute;t&eacute;</td>
            <td class='EnteteTab' style='width:70px;'></td>
        </tr>
        <?php

        $sql = "SELECT DISTINCT liaisoncompte.LICO_NUM ,liaisoncompte.LICO_nouvcompte,station.STA_SARL, comptes.numero, comptes.libelle, comptes.code_compte FROM liaisoncompte JOIN comptes ON liaisoncompte.LICO_code_compte = comptes.code_compte left join station on station.STA_NUM = liaisoncompte.STA_NUM WHERE liaisoncompte.CAB_NUM='" . $User->Var["CAB_NUM"] . "' order by STA_SARL,LICO_nouvcompte ";
        $result = Database::query($sql);
        while ($ligneresult = Database::fetchArray($result)) {
            if ($css == "bdlignepaireTD")
                $css = "bdligneimpaireTD ";
            else
                $css = "bdlignepaireTD";

            if (!$ligneresult['STA_SARL']) {
                $ligneresult['STA_SARL'] = "Toutes";
            }

            echo "<tr class='$css'><td align='center' class='bolder'>" . $ligneresult['LICO_nouvcompte'] . "</td><td align='left'><b>" . $ligneresult['numero'] . "</b> - " . utf8_encode($ligneresult['libelle']) . "</td>";
            echo "<td align='left'>" . $ligneresult['STA_SARL'] . "</td>";
            echo "<td style='text-align:center;'><a href='../LiaisonComptable/liaisoncomptable.php?LICO_NUM=" . $ligneresult['LICO_NUM'] . "'>Modifier</a></td></tr>";
        }

        ?>

    </table>
</div>

<?php
include("../include/pied.inc.php");

if ($error) {
    if ($error == 1)
        $mes = "Le compte inconnu est invalide.";

    if ($error == 2)
        $mes = "Le compte inconnu doit comporter 7 chiffres.";
    ?>
    <script type="text/javascript" language="javascript">
        Ext.MessageBox.show({
            title: 'Erreur',
            msg: '<?php echo $mes; ?>',
            buttons: Ext.MessageBox.OK
        });
    </script>
    <?php
}
?>
