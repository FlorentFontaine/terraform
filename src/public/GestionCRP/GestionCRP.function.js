/**
 * @author Myriam
 * @since 22/05/2014
 * @name CopieCRP
 * @param ObjJQuery
 */
function CopieCRP(ObjJQuery) {
    
    var ObjLigneTableau = $(ObjJQuery).parent();
    var FormGet = $(ObjLigneTableau).find(":input").serialize();

    //    alert(FormGet);

    /** REQUETE AJAX **/
    $.ajax({
        url:"GestionCRP.ajax.php",
        type: "POST",
        async: false,
        data: "action=copie_crp&"+FormGet,
        success: function(retour){
            retour = $.parseJSON(retour);

            if(!retour.ERROR)
            {
                //Traitement final à effectuer
                $("#maDIV_POPUP_INFO").html('Le CRP pr&eacute;c&eacute;dent a bien &eacute;t&eacute; recopi&eacute;.');
                $("#maDIV_POPUP_INFO").dialog({
                    title: "Copie CRP",
                    width: "200",
                    height: "100",
                    modal: false,
                    resizable: false,
                    buttons:{
                        "Fermer": function(){
                            $(this).dialog("close");
                            window.location = "index.php?page=form&CRP_NUM="+retour.CRP_NUM;
                        }
                    }
                });
                
                $(ObjJQuery).dialog('close');
            }
            else
            {
                //Affichage de l'erreur rencontr&eacute;e lors du traitement AJAX
                $("#maDIV_POPUP_INFO").html(retour.ERROR);
                $("#maDIV_POPUP_INFO").dialog({
                    title: "Erreur - Copie CRP",
                    width: "250",
                    height: "auto",
                    modal: false,
                    resizable: false,
                    buttons:{
                        "Fermer": function(){
                            $(this).dialog("close");
                        }
                    }
                });
            }
        }
    });
    
}



/**
 * @author Myriam
 * @since 21/05/2014
 * @name modifDateCRP
 * @param ObjJQuery
 */
function modifDateCRP(ObjJQuery) {
    
    var ObjLigneTableau = $(ObjJQuery).parent();
    var FormGet = $(ObjLigneTableau).find(":input").serialize();

    //    alert(FormGet);

    /** REQUETE AJAX **/
    $.ajax({
        url:"GestionCRP.ajax.php",
        type: "POST",
        async: false,
        data: "action=ajout_crp&modif_date=1&"+FormGet,
        success: function(retour){
            retour = $.parseJSON(retour);

            if(!retour.ERROR)
            {
                //Traitement final à effectuer
                $("#maDIV_POPUP_INFO").html('Dates du CRP modifi&eacute;es');
                $("#maDIV_POPUP_INFO").dialog({
                    title: "Modification CRP",
                    width: "250",
                    height: "150",
                    modal: false,
                    resizable: false,
                    buttons:{
                        "Fermer": function(){
                            $(this).dialog("close");
                            window.location = "index.php?page=form&CRP_NUM="+retour.CRP_NUM;
                        }
                    }
                });
                
                $(ObjJQuery).dialog('close');
            }
            else
            {
                //Affichage de l'erreur rencontr&eacute;e lors du traitement AJAX
                $("#maDIV_POPUP_INFO").html(retour.ERROR);
                $("#maDIV_POPUP_INFO").dialog({
                    title: "Erreur - Modification CRP",
                    width: "200",
                    height: "200",
                    modal: false,
                    resizable: false,
                    buttons:{
                        "Fermer": function(){
                            $(this).dialog("close");
                        }
                    }
                });
            }
        }
    });
    
}



/**
 * @author Myriam
 * @since 21/05/2014
 * @name load_Listener
 * @param 
 */
function load_Listener() {
    
    /** Datepicker: Configuration du DATE PICKER    **/
    var months = ['Janvier','F&eacute;vrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre'];
    var days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    var daysShort = ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'];
    var monthsShort = ['Jan','F&eacute;v','Mar','Avr','Mai','Juin','Juil','Aou','Sept','Oct','Nov','Dec'];
  
  $(".datepicked").each(function(){
      
      $(this).datepicker({
        dateFormat: 'dd/mm/yy',
        monthNames: months,
        dayNames: days,
        dayNamesMin: daysShort,
        monthNamesShort: monthsShort,
        firstDay: 1,
        changeMonth: true,
        changeYear: true,
        duration: 0
    });
    
  });
    
    
}


/**
 * @author Myriam
 * @since 26/05/2014
 * @name New_CRP
 * @param parameters
 */
function New_CRP() {
    
    $.ajax({
            url: "GestionCRP.ajax.php",
            type: "POST",
            async: false,
            data: {
                action: "HTML_NewCRP"
                        
            },
            success: function(retour) {
                retour = $.parseJSON(retour);
                
                if (!retour.ERROR)
                {  
                    //Traitement final à effectuer
                    $("#maDIV_POPUP").html(retour.html);
                    load_Listener();
                    $("#maDIV_POPUP").dialog({
                        title: "Nouveau CRP",
                        width: "auto",
                        height: "auto",
                        modal: true,
                        resizable: false,
                        buttons: {
                            
                            "Valider": function() {
                                addCRP(this);
                                
                            },
                            "Annuler": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    
                }
                else
                {
                    //Affichage de l'erreur rencontr&eacute;e lors du traitement AJAX
                    $("#maDIV_POPUP").html(retour.ERROR);
                    $("#maDIV_POPUP").dialog({
                        title: "monTitre",
                        width: "maWidth",
                        height: "maHeight",
                        modal: trueFalse,
                        resizable: trueFalse2,
                        buttons: {
                            "Fermer": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
            }
        });
    
}

