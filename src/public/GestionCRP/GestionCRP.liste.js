/*
 * Initialisation de Gestion CRP Liste
 */
$(document).ready(function(){
    init_GestionCRP_liste();
});

function init_GestionCRP_liste()
{
    
    
   if($("#new_crp_click").val()==1) {
        New_CRP();
   }


    $("#new_crp").click(New_CRP);

    $(".modif_date_CRP").click(function(){
        
        var CRP_NUM = $(this).attr("id").replace("modif_","");
        
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
    });
}


/**
 * @author Myriam
 * @since 21/05/2014
 * @name addCRP
 * @param ObjJQuery
 */
function addCRP(ObjJQuery) {
    
    var ObjLigneTableau = $(ObjJQuery).parent();
    var FormGet = $(ObjLigneTableau).find(":input").serialize();

    //    alert(FormGet);

    /** REQUETE AJAX **/
    $.ajax({
        url:"GestionCRP.ajax.php",
        type: "POST",
        async: false,
        data: "action=ajout_crp&"+FormGet,
        success: function(retour){
            retour = $.parseJSON(retour);

            if(!retour.ERROR)
            {
                //Traitement final à effectuer
                $("#maDIV_POPUP_INFO").html('Nouveau CRP cr&eacute;&eacute;');
                $("#maDIV_POPUP_INFO").dialog({
                    title: "Ajout CRP",
                    width: "200",
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
                    title: "Erreur - Ajout CRP",
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

