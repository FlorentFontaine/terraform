<div class="div-center">
    <div class="titresection">Liste des cabinets comptables</div>
</div>
<div id="container">
    <?php if (isset($_SESSION["messageCabinetComptable"])) { ?>
        <div class="div-center">
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION["messageCabinetComptable"]; ?>
            </div>
        </div>
    <?php } ?>
    <div id="filters">
        <div></div>
        <a style="float: right" class="button-spring" href="?action=new">Ajouter un Cabinet Comptable</a>
    </div>
    <div id="listContainer">
        <div class="tabhead">
            <table>
                <thead>
                <tr class="entete">
                    <th style="min-width: 160px">Cabinet comptable</th>
                    <th style="min-width: 50px">CP</th>
                    <th style="min-width: 120px">Ville</th>
                    <th style="width: 100px">T&eacute;l</th>
                    <th style="width: 180px">Fax</th>
                    <th style="min-width: 50px; width: 50px">Format compta</th>
                    <th style="min-width: 70px; width: 90px"></th>
                </tr>
                </thead>
                <tbody>
                <?php
                    $i = 0;
                    if(!empty($cabinetComptable)) {
                        foreach ($cabinetComptable as $cabinet) {
                            ?>
                            <tr style="height: 40px;" class="<?php echo $i % 2 ? 'impaire' : ''?>">
                                <td><?php echo utf8_encode($cabinet["CAB_NOM"]) ?></td>
                                <td><?php echo $cabinet["CAB_CP"] ?></td>
                                <td><?php echo $cabinet["CAB_VILLE"] ?></td>
                                <td><?php echo $cabinet["CAB_TEL"] ?></td>
                                <td><?php echo $cabinet["CAB_FAX"] ?></td>
                                <td><?php echo $cabinet["BAF_LIBELLE"] ?></td>
                                <td style='vertical-align: middle !important;height:10px;text-align:center;'>
                                    <a style='font-size: 10px;' href="?action=<?php echo $cabinet["CAB_NUM"] ?>">Modifier</a>
                                </td>
                            </tr>
                            <?php
                            $i++;
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" style="text-align: center">Aucun cabinet comptable</td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
            </table>
        </div>
        <div class="tabresults">
            <p>Nombre de r&eacute;sultats : <span id="nb"><?php echo count($cabinetComptable) ?></span></p>
        </div>
    </div>
</div>
</table>
