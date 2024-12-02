<?php

use Facades\Modules\Commentaire\Commentaire;
use Helpers\StringHelper;

global $User, $colorSituation;
?>

<script type="text/javascript">
    function secureLocation(btn, opt) {
        document.location.href = "?optall=" + opt;
    }

    $(document).ready(function () {
        // Sélectionnez toutes les divs cibles que vous souhaitez observer
        const targetDivs = $('.dropdown-content');

        // Options pour l'observateur
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5
        };

        // Fonction de rappel pour traiter les changements d'intersection
        const callback = (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // La div est visible, changer le background de son parent
                    let parent = $(entry.target).prev();
                    parent.css("background-color", "#7CB9E8").css("color", "white");
                    parent.removeClass('dropbtn');
                    parent.addClass('dropbtn-visible');

                } else {
                    // La div n'est pas visible, retirer le background de son parent
                    let parent = $(entry.target).prev();
                    parent.css("background-color", "transparent").css("color", "#09549C");
                    parent.removeClass('dropbtn-visible');
                    parent.addClass('dropbtn');
                }
            });
        };

        // Créer une instance de l'observateur pour chaque div cible
        targetDivs.each(function () {
            const observer = new IntersectionObserver(callback, options);
            observer.observe(this);
        });
    });
</script>

<ul id="navbar">
    <li>
        <a href="
    <?php
        if ($_SESSION["LOCKERS_MAIL"] != $_SESSION["Utilisateur"]["Mail"]) {
            echo '../login/login.php?retrieveAdmin=1';
        } elseif (isset($_SESSION["NB_ACCOUNTS"]) && $_SESSION["NB_ACCOUNTS"] > 1) {
            echo '../login/chooseAccount.php';
        } else {
            echo '../login/login.php?logout=1';
        }
        ?>" class="coupure">
            <img title="D&eacute;connexion" src="../images/logout.gif" alt="deconnexion"/>
        </a>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)" class="dropbtn">Report</a>
        <div class="dropdown-content">
            <?php
            if (isset($_SESSION["Utilisateur"]["LockersAdmin"])) { ?>
                <a href="../GESTION/index.php">
                    Acc&eacute;der &agrave; l'administration
                </a>
                <hr/>
            <?php }
            if (!$_SESSION["User"]->getAut("station", "liste")) { ?>
                <a href="../StationBack/Liste.php?notselect=1">
                    Liste des soci&eacute;t&eacute;s
                </a>
            <?php }
            if (!$_SESSION["User"]->getAut("lieu", "liste")) { ?>
                <a href="/pdv">
                    Liste des PDV
                </a>
            <?php }
            if (!$_SESSION["User"]->getAut("station", "liste")) { ?>
                <hr/>
                <a href="../SuiviBack/Liste.php?notselect=1">
                    Suivi d'activit&eacute;
                </a>
            <?php }
            if ($_SESSION["User"]->Type == "comptable") { ?>
                <hr/>
                <a href="../LiaisonComptable/liaisoncomptable.php?notselect=1">
                    Equivalences comptes
                </a>
            <?php } ?>
        </div>
    </li>
    <li class="dropdown">
        <a href="javascript:void(0)" class="dropbtn coupure">Etude</a>
        <div class="dropdown-content">
            <a href="../BenchMark/Liste.php?etude=consolidation&notselect=1">
                Consolidation
            </a>
            <a href="../BenchMark/Liste.php?etude=benchmark&notselect=1">
                Benchmark
            </a>
        </div>
    </li>
    <?php if ($_SESSION["station_DOS_NUM"]) { ?>

        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Ma Soci&eacute;t&eacute;</a>
            <div class="dropdown-content">
                <a href="../EtatCivil/index.php">
                    Etat civil
                </a>
                <a href="../GardeBack/Garde.php">
                    Informations Soci&eacute;t&eacute;
                </a>
            </div>
        </li>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Tableau de Gestion</a>
            <div class="dropdown-content">
                <a href="../RenseignementBack/Liste.php">
                    Renseignements compl&eacute;mentaires
                </a>
                <hr/>
                <a href="../synthese/Liste.php?cumul=1">
                    Synth&egrave;se
                </a>
                <a href="../SyntheseProjection/Liste.php">
                    Projection
                </a>
                <hr/>
                <a href="../ChargesMensuellesBack/Liste.php?Produits=1">
                    Produits de l'exercice
                </a>
                <a href="../MargeBack/Liste.php">
                    Calcul des marges
                </a>
                <a href="../ChargesMensuellesBack/Liste.php">
                    Charges de l'exercice
                </a>
                <hr/>
                <a href="../Bilan/Liste.php">
                    Bilan
                </a>
                <hr/>
                <a href="../ChargesDetailBack/Liste.php?Produits=1">
                    D&eacute;tail des comptes produits
                </a>
                <a href="../ChargesDetailBack/Liste.php">
                    D&eacute;tail des comptes charges
                </a>
                <?php if ($_SESSION["NbAno"]) { ?>
                    <hr/>
                    <a href="../Anomalie/MAnomalie.php">
                        <b style="color:red">Anomalies</b>
                    </a>
                <?php } ?>
            </div>
        </li>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Gestion CRP</a>
            <div class="dropdown-content">
                <a href="../GestionCRP/index.php?page=nouveau_crp">
                    Nouveau CRP
                </a>
                <hr/>
                <a href="../GestionCRP/index.php">
                    Liste des CRP
                </a>
                <a href="../GestionCRP/index.php?page=crp_en_cours">
                    Modifier le dernier CRP
                </a>
                <hr/>
                <a href="../PrevBack/Liste.php">
                    Consulter le CRP actif
                </a>
                <a href="../ObjectifSARL/?param1=Produits">
                    Objectifs chiffre d'affaires
                </a>
                <a href="../ObjectifSARL/?param1=Produits&param2=1">
                    Objectifs marges
                </a>
                <a href="../ObjectifSARL/?param1=Charges">
                    Objectifs charges
                </a>
            </div>
        </li>


        <?php if ($_SESSION['MODULES'][\Services\ModuleService::COMMENTAIRE]) { ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Commentaire</a>
                <div class="dropdown-content">
                    <?php if ($_SESSION["NbAno"]) { ?>
                        <a href="/commentaire/section/synthese">
                            1. Synth&egrave;se
                        </a>
                        <a href="/commentaire/section/produits">
                            2. Produits
                        </a>
                        <a href="/commentaire/section/masse-salariale">
                            3. Masse Salariale
                        </a>
                        <a href="/commentaire/section/charges">
                            4. Charges
                        </a>
                        <a href="/commentaire/section/bilan">
                            5. Bilan
                        </a>
                        <a href="/commentaire/section/previsualisation">
                            Pr&eacute;visualisation
                        </a>
                        <hr />
                    <?php } ?>
                    <a href="/commentaire/export" target="_blank">
                        Exporter les commentaires
                    </a>
                </div>
            </li>
        <?php } ?>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Balance</a>
            <div class="dropdown-content">
                <?php
                if (isset($correctionBal) && $correctionBal) {
                    $correctionBalJava = "true";
                } else {
                    $correctionBalJava = "false";
                } ?>
                <a href="/BalanceBack/Liste.php?classe=all" onclick="changeClasse('all',<?php echo $correctionBalJava; ?>)">
                    Tout
                </a>
                <a href="/BalanceBack/Liste.php?classe=1" onclick="changeClasse('1',<?php echo $correctionBalJava; ?>)">
                    Classe 1
                </a>
                <a href="/BalanceBack/Liste.php?classe=2" onclick="changeClasse('2',<?php echo $correctionBalJava; ?>)">
                    Classe 2
                </a>
                <a href="/BalanceBack/Liste.php?classe=3" onclick="changeClasse('3',<?php echo $correctionBalJava; ?>)">
                    Classe 3
                </a>
                <a href="/BalanceBack/Liste.php?classe=4" onclick="changeClasse('4',<?php echo $correctionBalJava; ?>)">
                    Classe 4
                </a>
                <a href="/BalanceBack/Liste.php?classe=5" onclick="changeClasse('5',<?php echo $correctionBalJava; ?>)">
                    Classe 5
                </a>
                <a href="/BalanceBack/Liste.php?classe=60-61" onclick="changeClasse('60-61',<?php echo $correctionBalJava; ?>)">
                    Classe 60-61
                </a>
                <a href="/BalanceBack/Liste.php?classe=62-63" onclick="changeClasse('62-63',<?php echo $correctionBalJava; ?>)">
                    Classe 62-63
                </a>
                <a href="/BalanceBack/Liste.php?classe=64-65" onclick="changeClasse('64-65',<?php echo $correctionBalJava; ?>)">
                    Classe 64-65
                </a>
                <a href="/BalanceBack/Liste.php?classe=66-67" onclick="changeClasse('66-67',<?php echo $correctionBalJava; ?>)">
                    Classe 66-67
                </a>
                <a href="/BalanceBack/Liste.php?classe=68-69" onclick="changeClasse('68-69',<?php echo $correctionBalJava; ?>)">
                    Classe 68-69
                </a>
                <a href="/BalanceBack/Liste.php?classe=70" onclick="changeClasse('70',<?php echo $correctionBalJava; ?>)">
                    Classe 70
                </a>
                <a href="/BalanceBack/Liste.php?classe=74-79" onclick="changeClasse('74-79',<?php echo $correctionBalJava; ?>)">
                    Classe 74-79
                </a>
                <?php if (!$_SESSION["User"]->getAut("impression", "1")) { ?>
                    <hr/>
                    <a href="../BalanceBack/Liste.php?AnoSensErrone=1&impression=1">
                        Liste des comptes avec sens erron&eacute;s
                    </a>
                <?php } ?>
            </div>
        </li>
        <li class="dropdown">
            <a href="javascript:void(0)" class="dropbtn">Exercice</a>
            <div class="dropdown-content">
                <?php if (!$_SESSION["User"]->getAut("Balance", "FormStation")) { ?>
                    <a href="../StationBack/formulaire2.php">
                        Cr&eacute;er un nouvel exercice
                    </a>
                    <?php
                    $Deb = date("m/Y", strtotime(StringHelper::DateFr2MySql($_SESSION["station_DOS_DEBEX"])));
                    $Fin = date("m/Y", strtotime(StringHelper::DateFr2MySql($_SESSION["station_DOS_FINEX"])));

                    if ($Deb == $Fin) {
                        $Exercice = $Deb;
                    } else {
                        $Exercice = $Deb . " &rarr; " . $Fin;
                    }
                    ?>
                    <a href="../StationBack/formulaire2.php?UpdateDossier=<?php echo $_SESSION["station_DOS_NUM"]; ?>">
                        Modifier dates exercice <?php echo $Exercice; ?>
                    </a>
                    <hr/>
                <?php }

                $MesExo = station::GetAllExercice($_SESSION["station_STA_NUM"]);

                $Prem = true;
                foreach ($MesExo as $UnExo) {

                    $Deb = date("m/Y", strtotime(StringHelper::DateFr2MySql($UnExo["DOS_DEBEX"])));
                    $Fin = date("m/Y", strtotime(StringHelper::DateFr2MySql($UnExo["DOS_FINEX"])));

                    if ($Deb == $Fin) {
                        $Exercice = $Deb;
                    } else {
                        $Exercice = $Deb . " &rarr; " . $Fin;
                    }
                    ?>
                    <a href="../StationBack/open.php?DOS_NUM=<?php echo $UnExo["DOS_NUM"]; ?>">
                        <?php echo $Exercice; ?>
                        <?php if ($UnExo["DOS_NUM"] == $_SESSION["station_DOS_NUM"]) {
                            echo "<img src='../images/ok.png' width='10' alt='V'/>";
                        } ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </li>
        <li class="dropdown">
            <a href="../ImprimBack/Liste.php">Impression</a>
        </li>
        <?php if (!$_SESSION["User"]->getAut("outil", "outil")) { ?>
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Outils</a>
                <div class="dropdown-content">
                    <?php if (!$_SESSION["User"]->getAut("Balance", "import") && $_SESSION["ModifOK"]) { ?>
                        <a href="../BalanceBack/Liste.php?import=1">
                            Importer balance
                        </a>
                        <hr/>
                    <?php }

                    if (!$_SESSION["User"]->getAut("Balance", "correct") && $_SESSION["ModifOK"]) { ?>
                        <a
                            <?php if ($_SESSION["station_PREM_BAL"]) { ?>
                                href="#"
                                onclick="customAlert(
                            'Correction de balance','Veuillez importer la premi&egrave;re balance du dossier'
                            )"
                            <?php } else {
                                $href = "../BalanceBack/Liste.php?correctionBal=1&classe=";
                                $href .= isset($classe) && $classe ? $classe : "all";
                                $href .= !isset($initSessionBal) || !$initSessionBal ? "&initSessionBal=1" : "";
                                ?>
                                href="<?php echo $href ?>"
                            <?php } ?>
                        >
                            Correction balance r&eacute;cup&eacute;r&eacute;e
                        </a>
                        <?php
                        if ((
                                $_SESSION["station_STA_DERNDOS"] == $_SESSION["station_DOS_NUM"]
                                && $_SESSION["ModifOK"])
                            || $User->Infos["Type"] == "ADMIN") {
                            ?>
                            <a
                                    onmousedown="customAlert(
                        'Suppression balance',
                        'Supprimer la balance en cours ?'
                        + '<br/><br/>ATTENTION : <br/>Cette action est irr\xe9versible !',
                        function(){secureLocation('yes','suppbal')},
                        'YESNO'
                    )"
                                    href="#">
                                Supprimer la derni&egrave;re balance de l'exercice
                            </a>
                        <?php }

                        ?>
                        <hr/>
                    <?php } ?>
                    <?php if (!$_SESSION["User"]->getAut("option", "copiebal") && $_SESSION["ModifOK"]) { ?>
                        <a
                        onmousedown="customAlert(
                            'Copie des taux de marge du pr&eacute;visionnel',
                            'Copier les taux de marge du pr&eacute;visionnel dans les renseignements compl&eacute;mentaires ?',
                            function(btn){ secureLocation(btn,'copieTM') },
                            'YESNO'
                        )"
                                href="#"
                        >
                            Copier les taux de marge du pr&eacute;visionnel
                            dans les renseignements
                        </a>
                        <a
                        onmousedown="customAlert(
                            'Copie des stocks initiaux',
                            'Copier les stocks initiaux de la balance dans les renseignements compl&eacute;mentaires ?',
                            function(btn){ secureLocation(btn,'copieSTI') },
                            'YESNO'
                        )"
                                href="#"
                        >
                            Copier stocks initiaux dans les renseignements
                        </a>
                        <a
                        onmousedown="customAlert(
                            'Copie des stocks finaux',
                            'Copier les stocks finaux de la balance dans les renseignements compl&eacute;mentaires ?',
                            function(btn){ secureLocation(btn,'copieSTF') },
                            'YESNO'
                        )"
                                href="#"
                        >
                            Copier stocks finaux dans les renseignements
                        </a>
                        <hr/>
                    <?php } ?>
                    <a href="../RenseignementBack/Liste.php?OubliMarge=1"
                       style="<?php echo $_SESSION["AnoStyle"]["oublimarge"] ?? '' ?>">
                        Recherche oubli % de marge
                    </a>
                    <a href="../RenseignementBack/Liste.php?PrevTxModifie=1"
                       style="<?php echo $_SESSION["RemStyle"]["PrevTxModifie"] ?? '' ?>">
                        Recherche les taux de marge diff&eacute;rents du pr&eacute;visionnel
                    </a>
                    <a href="../RenseignementBack/Liste.php?AnoStockFinalZero=1"
                       style="<?php echo $_SESSION["AnoStyle"]["AnoStockFinalZero"] ?? '' ?>">
                        Recherche oubli saisie stocks finaux
                    </a>
                    <a href="../RenseignementBack/Liste.php?AnoStockInit=1"
                       style="<?php echo $_SESSION["AnoStyle"]["StockInit"] ?? '' ?>">
                        Recherche erreur stocks initiaux
                    </a>
                    <a href="../RenseignementBack/Liste.php?AnoStockFinal=1"
                       style="<?php echo $_SESSION["AnoStyle"]["StockFinal"] ?? '' ?>">
                        Recherche erreur stocks finaux
                    </a>
                    <a href="../RenseignementBack/Liste.php?AnoVariationStock=1"
                       style="<?php echo $_SESSION["AnoStyle"]["VariationStock"] ?? '' ?>">
                        Recherche erreur var. stocks
                    </a>
                    <hr/>
                    <a href="../RenseignementBack/Liste.php?AnoTauxSup100=1"
                       style="<?php echo $_SESSION["AnoStyle"]["tauxsup100"] ?? '' ?>">
                        Recherche % de marge sup. &agrave; 100
                    </a>
                    <a href="../BalanceBack/Liste.php?AnoSensErrone=1"
                       style="<?php echo $_SESSION["AnoStyle"]["AnoSens"] ?? '' ?>">
                        Recherche compte avec sens erron&eacute;
                    </a>
                    <?php if (!$_SESSION["User"]->getAut("option", "effaceprev") && $_SESSION["ModifOK"]) { ?>
                        <hr/>
                        <a
                                onmousedown="customAlert(
                            'Effacement du pr&eacute;visionnel',
                            'Effacer le pr&eacute;visionnel ?',
                            function(btn){secureLocation(btn,'delprevprod')},
                            'YESNO'
                        )"
                                href="#">
                            Effacer le pr&eacute;visionnel actif
                        </a>
                        <a
                                onmousedown="customAlert(
                            'Recalculer historique pr&eacute;visionnel',
                            'Applique le pr&eacute;visionnel en recalculant les prorata potentiels de d&eacute;but et fin d\'exercices<br />ATTENTION : Cette action est irr&eacute;versible !',
                            function(btn){secureLocation(btn,'refactprev')},
                            'YESNO'
                        )"
                                href="#">
                            Appliquer le pr&eacute;visionnel sur le dossier
                        </a>
                    <?php }
                    if (!$_SESSION["User"]->getAut("imp", "export")) {
                        if ($_SESSION["NbAno"] && $_SESSION['Ano']["BALI_DATE_MAJBASE"]) {
                        ?>
                            <hr/>
                            <a style="color:red" href="../ImprimBack/Liste.php?optall=majb&validMAJBase=1">
                                Ex&eacute;cuter la MAJ Base
                            </a>
                        <?php
                        }
                    } ?>
                </div>
            </li>
        <?php } ?>

        <li class="dropdown">
            <a href="../BalanceDemo/bal.zip">
                Bal. de d&eacute;mo&nbsp;
                <svg xmlns="http://www.w3.org/2000/svg" height="12" width="12" viewBox="0 0 512 512">
                    <path d="M288 32c0-17.7-14.3-32-32-32s-32 14.3-32 32V274.7l-73.4-73.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l128 128c12.5 12.5 32.8 12.5 45.3 0l128-128c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L288 274.7V32zM64 352c-35.3 0-64 28.7-64 64v32c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V416c0-35.3-28.7-64-64-64H346.5l-45.3 45.3c-25 25-65.5 25-90.5 0L165.5 352H64zm368 56a24 24 0 1 1 0 48 24 24 0 1 1 0-48z"/>
                </svg>
            </a>
        </li>

        <?php if ($_SESSION["MoisHisto"] && $_SESSION["station_STA_NUM"]) { ?>
            <li style="float:right">
                <form method="post" class="form" name="formMoisDossier" id="formMoisDossier">
                    <div id="periode">
                        P&eacute;riode :
                    </div>
                    <div class="select">
                        <select name="moishisto" id="moishisto"
                                onchange='$("#formMoisDossier").submit();
                                this.style.background = this.options[this.selectedIndex].style.background'
                                style="font-size:12px;margin: 0;" tabindex="-1">
                            <?php
                            if ($MesDateImport = dbAcces::getBalImportM1($_SESSION["station_DOS_NUM"])) {
                                $MesDateImport = array_reverse($MesDateImport);
                                $MesDateAffiche = array();

                                foreach ($MesDateImport as $UneDate) {
                                    if ($UneDate["BALI_DATE_MAJBASE"] == 0
                                        && ($_SESSION["loged"] == "station"
                                            || ($_SESSION["User"]->Infos["Type"] == "agip"))
                                    ) {
                                        if (!$MesDateAffiche) {
                                            $MessageBox[0]["titre"] = "Report";
                                            $MessageBox[0]["message"] = "
                                                    Vous ne pouvez pas consulter le dossier,
                                                    <br/>le cabinet comptable n'a pas valid&eacute; le premier mois.
                                                ";

                                            if ($_SESSION["User"]->Infos["Type"] != "station") {
                                                $MessageBox[0]["fn"] = "
                                                        function(){
                                                            window.location.href='../StationBack/Liste.php'
                                                        }";
                                            } else {
                                                $MessageBox[0]["fn"] = "
                                                        function(){
                                                            window.location.href='../index.php'
                                                        }";
                                            }
                                        }
                                        break;
                                    }

                                    $MesDateAffiche[] = $UneDate;

                                    if ($UneDate["BALI_DATE_MAJBASE"] == 0) {
                                        break;
                                    }
                                }

                                $MesDateAffiche = array_reverse($MesDateAffiche);
                                $colorSituationSelect = $colorSituation;
                                $colorSituationSelect["BS"] = null;

                                foreach ($MesDateAffiche as $UneDate) {
                                    $select = "";
                                    if ($_SESSION["MoisHisto"] == $UneDate["BALI_MOIS"]) {
                                        $select = " selected='selected' ";
                                    }

                                    echo "<option value='" . $UneDate["BALI_MOIS"] . "' ";

                                    if ($colorSituationSelect[$UneDate["BALI_TYPE"]]) {
                                        echo " style='background-color:"
                                            . $colorSituationSelect[$UneDate["BALI_TYPE"]]["color"]
                                            . "' ";
                                    }

                                    echo $select . ">"
                                        . date("m/Y", strtotime(str_replace("-00", "-01", $UneDate["BALI_MOIS"])))
                                        . "</option>";
                                }
                            } else {
                                $_SESSION["ioreport_maxMoisHisto"] = $_SESSION["MoisHisto"];
                                echo "<option value='"
                                    . $_SESSION["MoisHisto"]
                                    . "' >"
                                    . date("m/Y", strtotime(
                                        str_replace("-00", "-01", $_SESSION["MoisHisto"])
                                    ))
                                    . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <script type="text/javascript">
                        let selectMois = document.getElementById("moishisto");
                        selectMois.style.background = selectMois.options[selectMois.selectedIndex].style.background;
                    </script>
                </form>
            </li>
            <li style="float:right">
                <a
                        style="position:relative;padding-right: 40px;"
                        href="../Anomalie/MAnomalie.php">
                    Anomalies :
                    <div class="notif-ano<?php if (!$_SESSION["NbAno"]) { ?>-ok<?php } ?>">
                        <?php echo (int)$_SESSION["NbAno"]; ?>
                    </div>
                </a>
            </li>
        <?php } ?>
    <?php } ?>
</ul>
