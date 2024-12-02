<?php
use Helpers\StringHelper;
use htmlClasses\TableV2;
if(!$EntetePiedFalse)
{
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="../style.css" type="text/css" media="screen"/>
    <link rel="stylesheet" type="text/css" href="../print.css" media="print"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <title>Synthese</title>
    <link rel="icon" type="image/png" href="../images/favicon/favicon.ico">


</head>

<body>



<?php
include("../include/entete.inc.php");
?>
<?php

}//entetepied
$TitleTable = "Projection";
$TitleTable .= " - ".StringHelper::DateComplete(str_replace("-00","-01",$_SESSION["MoisHisto"]))." - ".$_SESSION["station_BALI_TYPE_exp"];
?>



<table id="tab_synthese" dir="IMP_PDF;TITLETABLE:SYNTHESE;FREEZEPLAN:B5;HEIGHT:27;FITHEIGHT:1;ORIENTATION:LANDSCAPE;" style="width:0px" class="tabBalance" bordercolordark="#000000" bordercolorlight="#000000" align="center" id="tab_<?php echo $Type ?>">
    <thead>
    <?php echo EnteteTab::HTML_EnteteTab(array("Intitule"=>$TitleTable,"colspanCenter"=>6,"colspanRight"=>4)); ?>


    <tr class="EnteteTab">
        <td class="tdflotte" width="230"><div class="div180"></div></td>
        <td class="tdflotte colvide" width="1"></td>
        <td width="60" class="tdflotte">Pr&eacute;visionnel<div style="width: 70px;height:0px;"></div></td>
        <td width="60" class="tdflotte">Projection avec<br >Pr&eacute;visionnel<div style="width: 70px;height:0px;"></div></td>
        <td width="60" class="tdflotte">Projection avec<br >R&eacute;alis&eacute;<div style="width: 70px;height:0px;"></div></td>
        <td class="tdflotte colvide" width="1"></td>
        <td width="60" class="tdflotte">Ecart<br />Proj. Pr&eacute;v.<br /> / Pr&eacute;v.<div style="width: 70px;height:0px;"></div></td>
        <td width="60" class="tdflotte">Ecart en %<br />Proj. Pr&eacute;v.<br /> / Pr&eacute;v.<div style="width: 70px;height:0px;"></div></td>
        <td class="tdflotte colvide" width="1"></td>
        <td width="60" class="tdflotte">Ecart<br />Proj. R&eacute;al.<br /> / Pr&eacute;v.<div style="width: 70px;height:0px;"></div></td>
        <td width="60" class="tdflotte">Ecart en %<br />Proj. R&eacute;al.<br /> / Pr&eacute;v.<div style="width: 70px;height:0px;"></div></td>
    </tr>

    </thead>
    <tbody>
    <?php
    //Définition des date au format jour-mois-année

    $NbCols = 11;

    $opt["colorsigne"] = array(7,8,10,11);

    $LigneInsere = true;

    foreach($LignesCarb as $codecompte => $UneLigne)
    {
        if($LigneInsere)
        {
            if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
            else $cssligne = 'bdligneimpaireTD';
        }


        if(stristr($codecompte,"STOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lnstotal";
        }
        elseif(stristr($codecompte,"TOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lntotal";
        }
        elseif(stristr($codecompte,"TITRE"))
            $cssligne1 = "EnteteTab";
        else
            $cssligne1 = $cssligne;
        //$opt['debug_cletr'] = $codecompte;

        echo $LigneInsere = table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




    }

    echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

    $LigneInsere = true;

    $ca = [];

    foreach($LignesProduits as $codecompte => $UneLigne)
    {
        if($LigneInsere)
        {
            if ($cssligne=='bdlignepaireTD') $cssligne = 'bdligneimpaireTD';
            else $cssligne = 'bdlignepaireTD';
        }

        if(stristr($codecompte,"STOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lnstotal";
        }
        elseif(stristr($codecompte,"TOTAL"))
        {
            if("TOTALVENTES MARCHANDISES" == $codecompte) {
                $ca = $UneLigne;
            }
            //echo $codecompte."<br/>";
            $cssligne1 = "lntotal";
        }
        elseif(stristr($codecompte,"TITRE"))
            $cssligne1 = "EnteteTab";
        else
            $cssligne1 = $cssligne;
        //$opt['debug_cletr'] = $codecompte;

        if($codecompte != "TOTALCARBURANTS") //ne pas afficher la ligne Total carburant
            echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




    }

    echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

    $LigneInsere = true;

    foreach($LignesCharges as $codecompte => $UneLigne)
    {
        
        if($LigneInsere)
        {
            if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
            else $cssligne = 'bdligneimpaireTD';
        }


        if(stristr($codecompte,"STOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lnstotal";
        }
        elseif(stristr($codecompte,"TOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lntotal";
            // if($codecompte == "TOTALRESULTAT") {
            //     foreach($UneLigne as $position => $case) {
            //         foreach ($case as $value => $style) {
            //             if($value && in_array($position, [2,3,4,6,9])) {
            //                 $montantCa = array_key_first($ca[$position]);
            //                 $newKey = StringHelper::NombreFr(StringHelper::Texte2Nombre($montantCa) - StringHelper::Texte2Nombre($value));
            //                 $UneLigne[$position][$newKey] = $style;
            //                 unset($UneLigne[$position][$value]);
            //             }
            //         }
            //     }
            //     $montantPrev = array_key_first($UneLigne[2]);
            //     $montantPrevReal = array_key_first($UneLigne[3]);
            //     $montantRealReal = array_key_first($UneLigne[4]);

            //     $pourcEcartPrev = StringHelper::NombreFr((StringHelper::Texte2Nombre($montantPrevReal) - StringHelper::Texte2Nombre($montantPrev)) / StringHelper::Texte2Nombre($montantPrev) * 100) . " %";
            //     $pourcEcartReal = StringHelper::NombreFr((StringHelper::Texte2Nombre($montantRealReal) - StringHelper::Texte2Nombre($montantPrev)) / StringHelper::Texte2Nombre($montantPrev) * 100) . " %";

            //     $UneLigne[7] = [$pourcEcartPrev => $UneLigne[7][array_key_first($UneLigne[7])]];
            //     $UneLigne[10] = [$pourcEcartReal => $UneLigne[9][array_key_first($UneLigne[10])]];
            // }
        }
        elseif(stristr($codecompte,"TITRE"))
            $cssligne1 = "EnteteTab";
        else
            $cssligne1 = $cssligne;
        //$opt['debug_cletr'] = $codecompte;
        echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




    }

    echo table::getLine("",array("class"=>"bdlignepaireTD"),$NbCols,$opt);

    $LigneInsere = true;

    foreach($LignesONFR as $codecompte => $UneLigne)
    {
        if($LigneInsere)
        {
            if ($cssligne=='bdligneimpaireTD') $cssligne = 'bdlignepaireTD';
            else $cssligne = 'bdligneimpaireTD';
        }


        if(stristr($codecompte,"STOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lnstotal";
        }
        elseif(stristr($codecompte,"TOTAL"))
        {
            //echo $codecompte."<br/>";
            $cssligne1 = "lntotal";
        }
        elseif(stristr($codecompte,"TITRE"))
            $cssligne1 = "EnteteTab";
        else
            $cssligne1 = $cssligne;
        //$opt['debug_cletr'] = $codecompte;
        echo $LigneInsere =  table::getLine($UneLigne,array("class"=>$cssligne1),$NbCols,$opt);




    }



    ?>

    <?php //include '../shell_prev/ONFR.php'; ?>

    </tbody>
</table>




<?php
if(!$EntetePiedFalse)
{
?>
<?php
include("../include/pied.inc.php");
?>



</body>
</html>
<?php
}
?>