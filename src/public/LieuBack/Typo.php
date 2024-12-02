<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../dbClasses/User.php";

if ($Section == "Garde") {
    $ligneLieu = dbAcces::getLieu($_SESSION["station_LIE_NUM"]);
    $ligneLieu = $ligneLieu[$_SESSION["station_LIE_NUM"]];

    if ($ligneLieu['LIE_TYPO3'] == "oui") {
        echo "Typo 1 - ";
    }
    if ($ligneLieu['LIE_TYPO4'] == "oui") {
        echo "Typo 2 - ";
    }
    if ($ligneLieu['LIE_TYPO5'] == "oui") {
        echo "Typo 3 - ";
    }
    if ($ligneLieu['LIE_TYPO6'] == "oui") {
        echo "Typo 4 - ";
    }
    if ($ligneLieu['LIE_TYPO7'] == "oui") {
        echo "Typo 5 - ";
    }
    if ($ligneLieu['LIE_TYPO8'] == "oui") {
        echo "Typo 6 - ";
    }
    if ($ligneLieu['LIE_TYPO9'] == "oui") {
        echo "Typo 7 - ";
    }
    if ($ligneLieu['LIE_TYPO10'] == "oui") {
        echo "Typo 8 - ";
    }

} else {
    ?>

    <table style="margin-left:10px;text-align:left;width:100%" class="tabBalance" bordercolordark=#000000
           bordercolorlight=#000000>
        <tr class="EnteteTab">
            <td colspan="10">Cat&eacute;gorie du PDV</td>
        </tr>
        <tr class="EnteteTab">
            <td>
                <div class="div100"></div>
                Type PDV
            </td>
            <td>
                <div class="div100"></div>
                Mandat Lavage
            </td>
            <td>
                <div class="div100"></div>
                CA Boutique/an
            </td>
            <td>
                <div class="div100"></div>
                Superficie en m<sup>2</sup>
            </td>
            <td>
                <div class="div100"></div>
                Parking
            </td>
        </tr>
        <tr>
            <td>
                <label>
                    <input name="LIE_TYPO1" type="radio" value="Ville"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO1']) && $ligneLieu['LIE_TYPO1'] == "Autoroute") {
                                echo " checked='checked' ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>
                    />
                    &nbsp;Autoroute
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO2" type="radio" value="Oui"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO2']) && $ligneLieu['LIE_TYPO2'] == "Oui") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;Oui
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO3" type="radio" value="0 - 150"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO3']) && $ligneLieu['LIE_TYPO3'] == "0 - 150") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;0 - 150 K&euro;
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO4" type="radio" value="0 - 500"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO4']) && $ligneLieu['LIE_TYPO4'] == "0 - 500") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;0 - 500
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO5" type="radio" value="oui"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO5']) && $ligneLieu['LIE_TYPO5'] == "oui") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;oui
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <label>
                    <input name="LIE_TYPO1" type="radio" value="Autoroute"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO1']) && $ligneLieu['LIE_TYPO1'] == "Ville") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;Ville
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO2" type="radio" value="Non"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO2']) && $ligneLieu['LIE_TYPO2'] == "Non") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;Non
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO3" type="radio" value="150 - 300"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO3']) && $ligneLieu['LIE_TYPO3'] == "150 - 300") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;150 - 300 K&euro;
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO4" type="radio" value="500 - 800"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO4']) && $ligneLieu['LIE_TYPO4'] == "500 - 800") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;500 - 800
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO5" type="radio" value="non"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO5']) && $ligneLieu['LIE_TYPO5'] == "non") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;non
                </label>
            </td>
        </tr>

        <tr>
            <td>
                <label>
                    <input name="LIE_TYPO1" type="radio" value="P&eacute;riph&eacute;rique"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO1']) && $ligneLieu['LIE_TYPO1'] == "Campagne") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;Campagne
                </label>
            </td>
            <td rowspan="2">&nbsp;</td>
            <td>
                <label>
                    <input name="LIE_TYPO3" type="radio" value="+ de 300"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO3']) && $ligneLieu['LIE_TYPO3'] == "+ de 300") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;+ de 300 K&euro;
                </label>
            </td>
            <td>
                <label>
                    <input name="LIE_TYPO4" type="radio" value="800 - 1100"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO4']) && $ligneLieu['LIE_TYPO4'] == "800 - 1100") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;800 - 1100
                </label>
            </td>
        </tr>

        <tr>
            <td colspan="3">&nbsp;</td>
            <td>
                <label>
                    <input name="LIE_TYPO4" type="radio" value="+ de 1100"
                        <?php
                            if (isset($ligneLieu['LIE_TYPO4']) && $ligneLieu['LIE_TYPO4'] == "+ de 1100") {
                                echo " checked='checked'  ";
                            }

                            echo $_SESSION["User"]->getAut($Section, "create", "radio");
                        ?>/>
                    &nbsp;+ de 1100
                </label>
            </td>
        </tr>

    </table>

    <script type="text/javascript">
        $(document).ready(function () {
            $("input[type=radio]").each(function () {
                if (!$(this).attr("checked")) {
                    $(this).parent().css("color", "silver");
                }
            }).live("change", function () {
                $("[name=" + $(this).attr("name") + "]").each(function () {
                    $(this).parent().css("color", "silver")
                });

                $(this).parent().css("color", "black");
            });
        });
    </script>
<?php }
