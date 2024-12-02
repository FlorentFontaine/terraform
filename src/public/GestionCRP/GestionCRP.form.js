/*
 * Initialisation de Gestion CRP Form
 */
$(document).ready(function() {
    init_GestionCRP_form();
});

function init_GestionCRP_form()
{
    $("#btn_retour").click(function() {

        window.location = "index.php";
    });

    $("#copie_crp").click(function() {

        var CRP_NUM = $("#CRP_NUM").val();

        $.ajax({
            url: "GestionCRP.ajax.php",
            type: "POST",
            async: false,
            data: {
                action: "HTML_FormCopie_CRP",
                CRP_NUM: CRP_NUM

            },
            success: function(retour) {
                retour = $.parseJSON(retour);

                if (!retour.ERROR)
                {
                    //Traitement final à effectuer
                    $("#maDIV_POPUP").html(retour.html);
                    $("#maDIV_POPUP").dialog({
                        title: "Copie du CRP pr&eacute;c&eacute;dent",
                        width: "200",
                        height: "auto",
                        modal: true,
                        resizable: false,
                        buttons: {
                            "Valider": function() {
                                CopieCRP(this);
                            },
                            "Annuler": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                }
                else
                {
                    //Affichage de l'erreur rencontrée lors du traitement AJAX
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



    });
    
     $("#modif_date_CRP").click(function(){
        
        var CRP_NUM = $("#CRP_NUM").val();
        
        $.ajax({
            url: "GestionCRP.ajax.php",
            type: "POST",
            async: false,
            data: {
                action: "HTML_FormDateCRP",
                CRP_NUM : CRP_NUM
                        
            },
            success: function(retour) {
                retour = $.parseJSON(retour);
                
                if (!retour.ERROR)
                {  
                    //Traitement final à effectuer
                    $("#maDIV_POPUP").html(retour.html);
                    load_Listener();
                    $("#maDIV_POPUP").dialog({
                        title: "Modification dates CRP",
                        width: "auto",
                        height: "auto",
                        modal: true,
                        resizable: false,
                        buttons: {
                            
                            "Modifier": function() {
                                modifDateCRP(this);
                                
                            },
                            "Annuler": function() {
                                $(this).dialog("close");
                            }
                        }
                    });
                    
                }
                else
                {
                    //Affichage de l'erreur rencontrée lors du traitement AJAX
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
    });
}


