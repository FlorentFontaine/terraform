<?php

/**
 * Class HTML
 */
class HTML
{
    static function disabledElement($Type)
    {
        switch ($Type) {
            case "text":
                return " readonly='readonly' ";
            case "select":
            case "radio":
            default:
                return " disabled='disabled' ";
        }
    }
}
