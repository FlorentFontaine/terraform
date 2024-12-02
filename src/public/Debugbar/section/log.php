<?php

use Services\Debug\DebugService;

$errorType = DebugService::getLogErrorType();

$syntheseLogs = array();
foreach ($errorType as $key => $value) {
    $logs = $_SESSION["DEBUG"]["LOG"][$key];
    unset($logs["NB_ERROR"]);
    foreach ($logs as $logsvalue) {
        isset($syntheseLogs[$key][$logsvalue]) && $syntheseLogs[$key][$logsvalue]
            ? $syntheseLogs[$key][$logsvalue]++
            : $syntheseLogs[$key][$logsvalue] = 1;
    }
}
?>
<ul class="nav nav-tabs" id="nav-tab">
    <?php foreach ($errorType as $key => $value) { ?>
        <li class="nav-item">
            <a class="nav-link nav-link-tab" data="#<?= $key ?>" href="#"><?= $value['text'] ?>
                <span
                    class=" ml-2
                            badge
                            badge-pill
                            badge-<?= isset($value['nb']) && $value['nb'] ? "warning" : "success"  ?>">
                    <?= isset($value['nb']) && $value['nb'] ? $value['nb'] : 0 ?>
                </span>
            </a>
        </li>
    <?php } ?>
</ul>
<div class="tab-content" id="nav-tabContent">
    <?php
        foreach ($errorType as $key => $value) { ?>
        <div class="tab-pane" id="<?= $key ?>" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
            <table class="table table-striped table-sort">
                <?php
                    echo DebugService::getHeaderTable(array("Nombre", $value["text"]))
                ?>
                
                <tbody class="sortable">
                    <?php
                        if($_SESSION["DEBUG"]["LOG"][$key]["NB_ERROR"] == 0) {
                            echo "<tr>";
                                echo "<td colspan='2'>No error</td>";
                            echo "</tr>";
                        } else {
                            foreach ($syntheseLogs[$key] as $error => $nbError) {
                                echo "<tr>";
                                    echo "<td class='chiffre'>" . $nbError . "</td>";
                                    echo "<td>" . $error . "</td>";
                                echo "</tr>";
                            }
                        }
                    ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>

