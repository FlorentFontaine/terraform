<?php
use Helpers\StringHelper;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../ctrl/ctrl.php';
require_once __DIR__ . '/../dbClasses/station.php';
require_once __DIR__ . '/../dbClasses/AccesDonnees.php';
require_once __DIR__ . '/../htmlClasses/table.php';
require_once __DIR__ . '/../BenchMark/benchmark.class.php';

$_POST = $_SESSION["ioreport_Bench_POST"];
$_POST["stationinclude"] = false;
$_POST["LIE_NUM"] = false;

if ($_SESSION["User"]->Type == "Secteur") {
    $_POST["codeChefSecteur"] = $_SESSION["User"]->NumTableIdUser;
} elseif ($_SESSION["User"]->Type == "Region") {
    $_POST["codeChefRegion"] = $_SESSION["User"]->NumTableIdUser;
}

$MesStations = Benchmark::getStationInclude($_POST["MoisDeb"], $_POST["MoisFin"], true, $_POST);

?>
<div style="width: 100%;height: 100%;overflow: scroll;">
    <form method="post" name="formPlSt">
        <input type="hidden" name="consolider" value="1">
        <table style="width: 95%;background: white" border="1" align="center" bordercolordark=#000000
               bordercolorlight=#000000 class="tabForm">

            <tr class="EnteteTab">
                <td width="35%">PDV</td>
                <td width="35%">Soci&eacute;t&eacute;</td>
                <td width="15%">Dernier exercice</td>

            </tr>
            <?php
            if (!$MesStations) {
                echo "<tr><td colspan='10'>Aucun PDV</td></tr>";
            }

            $MaStaDef = '';

            foreach ($MesStations as $UneStation) {
                if (isset($cssligne) && $cssligne == 'bdlignepaireTD') {
                    $cssligne = 'bdligneimpaireTD';
                } else {
                    $cssligne = 'bdlignepaireTD';
                }


                if ($MaStaDef != $UneStation["LIE_NUM"]) {
                    $cssligne = 'bdlignepaireTD';
                    ?>
                    <tr class="" style="font-size: 15px">
                        <td nowrap="nowrap" colspan="3" align="left">
                            <?php echo utf8_encode($UneStation["LIE_NOM"]); ?>
                        </td>
                    </tr>
                    <?php
                }

                $MaStaDef = $UneStation["LIE_NUM"];
                ?>

                <tr class="<?php echo $cssligne; ?>">
                    <td nowrap="nowrap">
                        <?php echo utf8_encode($UneStation["LIE_NOM"]); ?></td>
                    <td nowrap="nowrap">
                        <?php echo utf8_encode($UneStation["STA_SARL"]); ?>
                    </td>
                    <td nowrap="nowrap" align="center">
                        <?php echo StringHelper::MySql2DateFr($UneStation["DOS_DEBEX"]); ?> - <?php echo StringHelper::MySql2DateFr($UneStation["DOS_FINEX"]); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
    </form>

</div>
