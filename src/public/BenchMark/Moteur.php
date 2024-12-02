<?php
use Helpers\StringHelper;

/** @var $etude string */
/** @var $TitleTable string */
/** @var $Min string */
/** @var $Max string */
/** @var $MoisDeb string */
/** @var $MoisFin string */
/** @var $NbDossier int */

?>

<form name="formBench" method="post">
    <?php
    if ($etude == "benchmark") {
        ?>
        <input type="hidden" name="MinMaxSta" value="<?php if ($_POST['LIE_NUM']) {
            echo $_POST['LIE_NUM'];
        } ?>">
        <?php
    }
    ?>

    <table style="border:1px solid gray;width:0" border="1" align="center" class="tabForm">
        <tr>
            <td class="EnteteTab TitreTable " colspan="7"
                style="text-align:center;font-weight:bold;border:none;padding: 10px"><?php echo $TitleTable; ?></td>
        </tr>
        <tr class="EnteteTab">
            <td colspan="2">Date d&eacute;but / fin</td>
            <td></td>
            <td>Type PDV</td>
            <td>Mandat Lavage</td>
            <td>CA Boutique/an</td>
            <td>Superficie en m2</td>
        </tr>

        <tr>
            <td colspan="2" align="center" valign="middle">
                <select name="MoisDeb" onchange="submitBenchForm()">
                    <?php
                    $DateCourante = $Max = date("Y-m-01", strtotime(str_replace("-00", "-01", $Max)));
                    $Min = date("Y-m-01", strtotime(str_replace("-00", "-01", $Min)));

                    while (strtotime($DateCourante) >= strtotime($Min)) {
                        $DateAff = date("Y-m-00", strtotime($DateCourante));
                        echo "<option value='" . $DateAff . "' ";

                        if ($DateAff == $MoisDeb) {
                            echo " selected='selected' ";
                        }

                        echo ">" . StringHelper::MySql2DateFr($DateAff) . "</option>";

                        $DateCourante = StringHelper::DatePlus($DateCourante, array("moisplus" => -1));
                    }
                    ?>
                </select>
                -
                <select name="MoisFin" onchange="submitBenchForm()">
                    <?php
                    $DateCourante = $Max;
                    while (strtotime($DateCourante) >= strtotime($Min)) {
                        $DateAff = date("Y-m-00", strtotime($DateCourante));
                        echo "<option value='" . $DateAff . "' ";

                        if ($DateAff == $MoisFin) {
                            echo " selected='selected' ";
                        }

                        echo ">" . StringHelper::MySql2DateFr($DateAff) . "</option>";

                        $DateCourante = StringHelper::DatePlus($DateCourante, array("moisplus" => -1));
                    }
                    ?>
                </select>

            </td>
            <td align="center" valign="middle" style="width: 80px" class="TitleMe">
                <input type="hidden" name="consoListeStation" id="consoListeStation" value="">
                <span style="width: 150px" title="Prendre dans la consolidation tous les points de vente avec des donn&eacute;es sur toute la p&eacute;riode.
\r\nSi cette case n'est pas coch&eacute;e, les points de vente n'ayant pas de donn&eacute;es sur toute la p&eacute;riode ne seront pas incluses.">
      <label>
        <input type="checkbox" name="NoStrict" <?php if (isset($_POST["NoStrict"]) && $_POST["NoStrict"]) {
            echo " checked='1' ";
        } ?> value="1"
               onclick="submit()"/>
        Non stricte
      </label>
      </span>

            </td>
            <td align="center">

                <select name="LIE_TYPO1" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO1"]) && $_POST["LIE_TYPO1"]) {
                        echo "<option value='" . $_POST["LIE_TYPO1"] . "'>" . $_POST["LIE_TYPO1"] . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="Autoroute">Autoroute</option>
                    <option value="Ville">Ville</option>
                    <option value="Campagne">Campagne</option>
                </select>

            </td>
            <td align="center">
                <select name="LIE_TYPO2" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO2"]) && $_POST["LIE_TYPO2"]) {
                        echo "<option value='" . $_POST["LIE_TYPO2"] . "'>" . $_POST["LIE_TYPO2"] . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="Oui">Oui</option>
                    <option value="Non">Non</option>
                </select>
            </td>

            <td align="center">
                <select name="LIE_TYPO3" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO3"]) && $_POST["LIE_TYPO3"]) {
                        echo "<option value='" . $_POST["LIE_TYPO3"] . "'>" . $_POST["LIE_TYPO3"] . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="0 - 150">0 - 150</option>
                    <option value="150 - 300">150 - 300</option>
                    <option value="+ de 300">+ de 300</option>
                </select>
            </td>

            <td align="center">
                <select name="LIE_TYPO4" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO4"]) && $_POST["LIE_TYPO4"]) {
                        echo "<option value='" . $_POST["LIE_TYPO4"] . "'>" . $_POST["LIE_TYPO4"] . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="0 - 500">0 - 500</option>
                    <option value="500 - 800">500 - 800</option>
                    <option value="800 - 1100">800 - 1100</option>
                    <option value="+ de 1100">+ de 1100</option>
                </select>
            </td>
        </tr>

        <tr class="EnteteTab">
            <td colspan="2">
                <div style="width:330px"></div>
                PDV <?php if ($etude == "benchmark") { ?>du Benchmark<?php } else { ?>concern&eacute;s<?php } ?></td>
            <td>
                <div class="div90"></div>
                Typo 5
            </td>
            <td>
                <div class="div90"></div>
                Typo 6
            </td>
            <td>
                <div class="div90"></div>
                Typo 7
            </td>
            <td>
                <div class="div90"></div>
                Typo 8
            </td>
            <td>
                <div class="div90"></div>
                Typo 9
            </td>
        </tr>

        <tr>
            <td colspan="2" align="center" valign="middle">
                <input type="button" value="<?php echo $NbDossier ?> PDV" id="selectPlSt" class="button-spring"
                       onclick="document.getElementById('consoListeStation').value=1;getStations('<?php echo $_GET["etude"] ?>')"/>

            </td>
            <td align="center" valign="middle">
                <select name="LIE_TYPO5" disabled="disabled" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO5"]) && $_POST["LIE_TYPO5"]) {
                        echo "<option value='" . $_POST["LIE_TYPO5"] . "'>" . TypoStr($_POST["LIE_TYPO5"]) . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="oui">&radic;</option>
                    <option value="non">x</option>
                </select>
            </td>
            <td align="center" valign="middle">
                <select name="LIE_TYPO6" disabled="disabled" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO6"]) && $_POST["LIE_TYPO6"]) {
                        echo "<option value='" . $_POST["LIE_TYPO6"] . "'>" . TypoStr($_POST["LIE_TYPO6"]) . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="oui">&radic;</option>
                    <option value="non">x</option>
                </select>
            </td>
            <td align="center" valign="middle">
                <select name="LIE_TYPO7" disabled="disabled" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO7"]) && $_POST["LIE_TYPO7"]) {
                        echo "<option value='" . $_POST["LIE_TYPO7"] . "'>" . TypoStr($_POST["LIE_TYPO7"]) . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="oui">&radic;</option>
                    <option value="non">x</option>
                </select>
            </td>

            <td align="center" valign="middle">
                <select name="LIE_TYPO8" disabled="disabled" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO8"]) && $_POST["LIE_TYPO8"]) {
                        echo "<option value='" . $_POST["LIE_TYPO8"] . "'>" . TypoStr($_POST["LIE_TYPO8"]) . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="oui">&radic;</option>
                    <option value="non">x</option>
                </select>
            </td>

            <td align="center" valign="middle">
                <select name="LIE_TYPO9" disabled="disabled" onchange="submitBenchForm()">
                    <?php if (isset($_POST["LIE_TYPO9"]) && $_POST["LIE_TYPO9"]) {
                        echo "<option value='" . $_POST["LIE_TYPO9"] . "'>" . TypoStr($_POST["LIE_TYPO9"]) . "</option>";
                    } ?>
                    <option value=""></option>
                    <option value="oui">&radic;</option>
                    <option value="non">x</option>
                </select>
            </td>

        </tr>

        <?php
        if ($etude == "benchmark") {
            ?>
            <tr class="EnteteTab">
                <td colspan="2">
                    <div style="width:300px"></div>
                    PDV de Comparaison
                </td>
                <td colspan="7"></td>
            </tr>
            <tr>
                <td align="center">
                    <select style="width: 95%" autocomplete="1" name="LIE_NUM" onchange="submitBenchForm()">
                        <option value="">Choisissez...</option>
                        <?php
                        $opt = array();

                        if ($_SESSION["User"]->Type == "Secteur") {
                            $opt = array("where" => " and lieu.codeChefSecteur = '" . $_SESSION["User"]->NumTableIdUser . "'");
                        } elseif ($_SESSION["User"]->Type == "Region") {
                            $opt = array("where" => " and lieu.codeChefRegion = '" . $_SESSION["User"]->NumTableIdUser . "'");
                        }

                        $MesLieux = dbAcces::getLieu(null, $opt);

                        foreach ($MesLieux as $LIE_NUM => $UnLieu) {
                            $selected = "";

                            if ($_POST["LIE_NUM"] == $LIE_NUM) {
                                $selected = "selected='1'";
                            }

                            echo "<option value='" . $LIE_NUM . "' $selected>" . $UnLieu['LIE_CODE'] . " - " . $UnLieu['LIE_NOM'] . "</option>";
                        }

                        ?>
                    </select>
                </td>
            </tr>
            <?php
        }
        ?>
    </table>
</form>
