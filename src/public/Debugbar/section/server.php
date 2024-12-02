<table class="table table-striped table-sort">
    <?php

    use Services\Debug\DebugService;

    echo DebugService::getHeaderTable(array("Key", "Type", "Value"));
    ?>

    <tbody class="sortable">
        <?php
        foreach ($_SESSION["DEBUG"]["SERVER"] as $key => $value) {
            echo "<tr>";
            echo "<td>" . $key . "</td>";
            echo "<td>" . gettype($value) . "</td>";
            echo "<td>" . $value . "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
