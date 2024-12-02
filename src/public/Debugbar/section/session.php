<?php


use Services\Debug\DebugService;

$table = "<table class='table table-striped'>
            <thead>
                <tr>
                    <th scope='col'>Field</th>
                    <th scope='col'>Type</th>
                    <th scope='col'>Value</th>
                </tr>
            </thead>";

echo $table;
foreach ($_SESSION as $key => $value) {
    if($key != "DEBUG") {
        DebugService::getTableImbrique($table, $key, $value);
    }
}
echo "</table>";
echo "<br>";
