<center>
    <table width="80%">
        <tr>
            <td>
                <table align="center" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000">
                    <tbody>
                        <?php

                        use htmlClasses\TableV2;

                        foreach ($MesLignes as $codecompte => $UneLigne) {
                            $cssligne1 = "";

                            if (stristr($codecompte, "STOTAL") || stristr($codecompte, "Poste")) {
                                $cssligne1 = "bolder";
                            } elseif (stristr($codecompte, "ENCADRE")) {
                                $cssligne1 = "EnteteTab";
                            }

                            echo table::getLine($UneLigne, array("class" => $cssligne1), 2);
                        } ?>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</center>

