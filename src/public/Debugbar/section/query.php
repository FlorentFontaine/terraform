<h3>Synthese</h3>
<table class="table table-striped table-sort">
    <?php

    use Services\Debug\DebugService;

    echo DebugService::getHeaderTable(array("Time", "Nombre de requete", "Trace"));
    ?>
    
    <tbody class="sortable">
        <?php
        foreach ($_SESSION["DEBUG"]["SYNTHESE_QUERY"] as $class => $synthese) {
            $showDetail = false;
            echo "<td>" . DebugService::formatTime($synthese["TOTAL_TIME"])  . "</td>";
            echo "<td>" . $synthese["NB_REQUEST"] . "</td>";
            echo "<td>";
            if(count($synthese["FILE"]) > 1) {
                $showDetail = true;
            }
            foreach ($synthese["FILE"] as $file => $callFile) {
                echo $class . " <strong>[" . $file . "]</strong>" .
                ($showDetail ? " (" . $callFile . ")" : "")  . "<br />";
            }
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<br />
<h3>Detail</h3>
<table class="table table-striped table-sort">
    <?php
        $fields = array("Time", "Trace", "Request");
        echo DebugService::getHeaderTable($fields)
    ?>
    <tbody class="sortable">
        <?php
        $queries = $_SESSION["DEBUG"]["QUERY"];
        unset($queries["NB_ERROR"]);
        unset($queries["START_TIME"]);
        unset($queries["TOTAL_TIME"]);
        unset($queries["TOTAL_TIME_FORMATTED"]);
        foreach ($queries as $key => $value) {
            echo "<tr>";
            echo "<td>" . $value["TIME"] . "</td>";
            echo "<td>" . $value["TRACE"] . "</td>";
            echo "<td>" . DebugService::prettifySql($value["REQUEST"]) . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

