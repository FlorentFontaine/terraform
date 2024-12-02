<?php

/** @var string $selected */
/** @var string $hrefPDVInactif */
/** @var \htmlClasses\TableV2 $tableau */

// Nom de la page
$title = "Liste des PDV";

include_once __DIR__ . "/../../include/htmlHeader.php";
include_once __DIR__ . "/../../include/entete.inc.php";
?>

<?php
if (!$_SESSION["User"]->getAut("lieu", "create")) { ?>
    <div style="text-align: center">
        <a href="../../LieuBack/formulaire.php?new=1&notselect=1">
            <button class="button-spring">
                Cr&eacute;er un PDV
            </button>
        </a>
    </div>
    <br/>
<?php } ?>

<form action="" method="post" id="formFiltresLieu" name="formFiltresLieu"
      style="display: flex; flex-direction: row; justify-content: center">

    <?php if ($_SESSION['User']->Type != "Secteur") { ?>
        <div>
            <?php
            $params = array(
                'value' => isset($_POST['codeChefSecteur']) && $_POST['codeChefSecteur'] ? $_POST['codeChefSecteur'] : '',
                'name' => 'codeChefSecteur',
                'style' => "width: 200px;",
                'onchange' => "document.forms.formFiltresLieu.submit();",
            );
            echo Filtres::cds($params);
            ?>
        </div>
    <?php } ?>

    &nbsp;

    <?php if ($_SESSION["agip_AG_NUM"]) { ?>
        <div>
            <?php
            $params = array(
                'value' => isset($_POST['CAB_NUM']) && $_POST['CAB_NUM'] ? $_POST['CAB_NUM'] : '',
                'name' => 'CAB_NUM',
                'style' => "width: 200px;",
                'onchange' => "document.forms.formFiltresLieu.submit();",
            );
            echo Filtres::cabinet($params);
            ?>
        </div>
    <?php } ?>

    &nbsp;

    <div class="custom-checkbox-wrapper">
        <input type="hidden" name="inactif" value="non">
        <input type="checkbox" <?= isset($_POST['inactif']) && $_POST['inactif'] === 'oui' ? "checked" : "" ?>
               onchange="document.forms.formFiltresLieu.submit();" name="inactif" id="inactif" value="oui" />
        <label for="inactif">PDV inactifs</label>
    </div>

</form>

<?php

echo $tableau->render();

include_once __DIR__ . "/../../include/pied.inc.php";
