<div class="div-center">
    <div class="titresection"><?php echo $cabinetComptable ? "Modification" : "Cr&eacute;ation" ?> d'un cabinets comptables</div>
</div>
<div id="container">
    <?php if (isset($_SESSION["messageCabinetComptable"])) { ?>
        <div class="div-center">
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION["messageCabinetComptable"]; ?>
            </div>
        </div>
    <?php } ?>
    <div id="filters" class="list-container-responsive" style="padding-bottom:35px;padding-right:0 !important;">
        <div></div>
        <a style="float: right" class="button-spring" href="./">Retour</a>
    </div>
    <div id="listContainer" class="div-center flex list-container-responsive border-form">
        <div class="bandeau">
            <div><?php echo $cabinetComptable ? "Modification" : "Cr&eacute;ation" ?> d'un cabinets comptables</div>
        </div>
        <form action="" method="post">
            <input type="hidden" name="CAB_NUM" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_NUM"] : null ?>" />
            <div class="flex">
                <div class="form-group">
                    <label for="CAB_NOM">Cabinet comptable:</label>
                    <input type="text" id="CAB_NOM" name="CAB_NOM" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_NOM"] : null ?>">
                </div>
                <div class="form-group">
                    <label for="CAB_ADR1">Adr1:</label>
                    <input type="text" id="CAB_ADR1" name="CAB_ADR1" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_ADR1"] : null ?>">
                </div>
            </div>
            <div class="flex">
                <div class="form-group">
                    <label for="CAB_ADR2">Adr2:</label>
                    <input type="text" id="CAB_ADR2" name="CAB_ADR2" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_ADR2"] : null ?>">
                </div>
                <div class="form-group">
                    <label for="CAB_CP">Cp:</label>
                    <input type="text" id="CAB_CP" name="CAB_CP" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_CP"] : null ?>">
                </div>
            </div>
            <div class="flex">
                <div class="form-group">
                    <label for="CAB_VILLE">Ville:</label>
                    <input type="text" id="CAB_VILLE" name="CAB_VILLE" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_VILLE"] : null ?>">
                </div>
                <div class="form-group">
                    <label for="CAB_TEL">T&eacute;l:</label>
                    <input type="text" id="CAB_TEL" name="CAB_TEL" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_TEL"] : null ?>">
                </div>
            </div>
            <div class="flex">
                <div class="form-group">
                    <label for="CAB_FAX">Fax:</label>
                    <input type="text" id="CAB_FAX" name="CAB_FAX" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_FAX"] : null ?>">
                </div>
                <div class="form-group">
                    <label for="CAB_MAIL">E-Mail:</label>
                    <input type="text" id="CAB_MAIL" name="CAB_MAIL" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_MAIL"] : null ?>">
                </div>
            </div>
            <div class="flex">
                <div class="form-group">
                    <label for="CAB_SITE">Web:</label>
                    <input type="text" id="CAB_SITE" name="CAB_SITE" class="custom-input-text" value="<?php echo $cabinetComptable ? $cabinetComptable["CAB_SITE"] : null ?>">
                </div>
                <div class="form-group">
                    <label for="BAF_NUM">Format compta:</label>
                    <select id="BAF_NUM" name="BAF_NUM" class="select">
                        <option value="">-- S&eacute;lectionner un format --</option>
                        <?php
                            foreach ($balanceFormal as $format) {
                                ?>
                                <option value="<?php echo $format["BAF_NUM"] ?>" <?php echo $cabinetComptable && $cabinetComptable["BAF_NUM"] == $format["BAF_NUM"] ? "selected" : "" ?>><?php echo $format["BAF_LIBELLE"] ?></option>
                                <?php
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="flex flex-center">
                <input type="submit" class="button-spring" value="Enregistrer" name="saveCabinetComptable">
            </div>
        </form>
        <?php if ($cabinetComptable && $cabinetComptable["CAB_NUM"]) { ?>
            <div>
                <form action="" method="post" id="deleteCabinetComptable">
                    <div class="flex flex-end">
                        <input type="hidden" name="CAB_NUM" value="<?php echo $cabinetComptable["CAB_NUM"] ?>" />
                        <?php if ($cabinetComptable && $cabinetComptable["CAB_NUM"] && $cabinetComptable["NbStation"] == 0) { ?>
                            <input class="button-spring" style="color:red;" value="Supprimer" id="buttonDeleteCabinetComptable" name="deleteCabinetComptable">
                        <?php } else { ?>
                            <input class="button-spring-disabled" title="Le cabinet est affect&eacute; &agrave; une ou plusieurs station." value="Supprimer" disabled>
                        <?php } ?>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
</div>
</table>

<div id="dialog"></div>

<script>
    $("#buttonDeleteCabinetComptable").click(function (e) {
        e.preventDefault();
        $("#dialog").html("Voulez-vous vraiment supprimer ce cabinet comptable ?");
        $("#dialog").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            modal: true,
            buttons: {
                "Supprimer": function () {
                    $(this).dialog("close");
                    $("#deleteCabinetComptable").submit();
                },
                "Annuler": function () {
                    $(this).dialog("close");
                }
            }
        });
    });
</script>
