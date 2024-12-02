<?php

class EnteteTab
{
    static function HTML_EnteteTab($param = array())
    {
        $HTML = '';

        if (!isset($param["colspanLeft"]) || !$param["colspanLeft"]) {
            $param["colspanLeft"] = 1;
        }

        if (!isset($param["colspanRight"]) || !$param["colspanRight"]) {
            $param["colspanRight"] = 2;
        }

        if (!isset($param["colspanCenter"]) || !$param["colspanCenter"]) {
            $param["colspanCenter"] = 0;
        }

        $param["colspanLigne"] = $param["colspanLeft"] + $param["colspanCenter"] + $param["colspanRight"];


        $HTML .= '<tr><td class="EnteteTab TitreTable" colspan="' . $param["colspanLigne"] . '" style="text-align:center;font-weight:bold;border:none;height:15px;" height="25">' . $param['Intitule'] . '</td></tr>';
        $HTML .= '<tr><td colspan="' . $param["colspanLigne"] . '" style="text-align:center;border:none"><div style="height:1px"></div></td></tr>';

        return $HTML;
    }
}
