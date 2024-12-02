<table class="table table-striped table-sort">
    <?php

    use Services\Debug\DebugService;

    echo DebugService::getHeaderTable(array("Key", "Type", "Value"))
    ?>
    
    <tbody class="sortable">
        <?php
        foreach ($_SESSION["DEBUG"]["RESPONSE"]["HEADER"] as $value) {
            $values = explode(":", $value);
            echo "<tr>";
            echo "<td>" . $values[0] . "</td>";
            echo "<td>string</td>";
            echo "<td>" . $values[1] . "</td>";
            echo "</tr>";
        }
        foreach ($_SESSION["DEBUG"]["RESPONSE"] as $key => $value) {
            if($key != "HEADER") {
                $values = explode(":", $value);
                echo "<tr>";
                echo "<td>" . $values[0] . "</td>";
                echo "<td>" . $values[1] . "</td>";
                echo "<td>" . $values[2] . "</td>";
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>






