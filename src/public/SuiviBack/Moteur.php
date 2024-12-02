<form method="post" action="" name="formSuivi">
    <input type="hidden" id="ChampTri" name="ChampTri"
           value="<?php if (isset($_POST["ChampTri"]) && $_POST["ChampTri"]) {
               echo $_POST["ChampTri"];
           } ?>"/>

    <table style="border:1px solid #000000;width: 500px" border="1" align="center" class="tabForm" bordercolordark=#000000 bordercolorlight=#000000>
        <tr class="EnteteTab">
            <td>Cabinet Comptable</td>
            <td>Chef de Secteur</td>
        </tr>
        <tr>
            <td>
                <?php
                global $User;

                if ($User->Infos['Type'] == "comptable") {
                    echo $User->Var['CAB_NOM'];
                } else {
                    $params = array(
                        'value' => isset($_POST['CAB_NUM']) && $_POST['CAB_NUM'] ? $_POST['CAB_NUM'] : '',
                        'name' => 'CAB_NUM',
                        'onchange' => "submit()",
                    );
                    echo Filtres::cabinet($params);
                } ?>
            </td>
            <td>
                <?php
                if ($User->Infos['Type'] == "Secteur") {
                    echo $User->Var["Nom"] . " " . $User->Var["Prenom"];
                } else {
                    $params = array(
                        'value' => isset($_POST['codeChefSecteur']) && $_POST['codeChefSecteur'] ? $_POST['codeChefSecteur'] : '',
                        'name' => 'codeChefSecteur',
                        'onchange' => "submit()",
                    );
                    echo Filtres::cds($params);
                } ?>
            </td>
        </tr>
    </table>
