
$(document).ready(function() {

    EnableDisable_Cluster();
    
    $("#STA_NUM_CLUSTER_MAITRE").change(function(){

        EnableDisable_Cluster();

    });

    $("#STA_NUM_CLUSTER_PARENT").change(function(){

        EnableDisable_Cluster();
    });

    // Chargement de la liste "gérants"
    gerant.init();

});

var gerant = {
    init: function() {
        // Chargement de la liste des gérants
        gerant.loadListe();

        // Evènements sur les boutons de modification
        $("#gerant").click(function() {
            var type = $(this).attr("data-type");
            var id = $("#GER_NUM").val() ? $("#GER_NUM").val() : null;

            gerant.getForm(type, id);
        });
    },
    loadListe: function() {
        var STA_NUM = $("#STA_NUM").val();
        $(".fieldGerant").html("");
        $(".fieldGerant").val("");

        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getListeGerants",
                STA_NUM: STA_NUM
            },
            success: function(retour) {
                var retour = $.parseJSON(retour);

                if(retour.datas && retour.datas.length == 1) {
                    var gerant = retour.datas[0];
                    $("#GER_NUM").val(gerant.USER_NUM);
                    $("#GER_MAIL").html(gerant.USER_MAIL);
                    $("#GER_NOM").html(gerant.USER_NOM);
                    $("#GER_PRENOM").html(gerant.USER_PRENOM);
                    $("#gerant").attr("data-type", "update");
                }
            }
        });
    },
    getForm: function(type, id, number) {
        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getForm",
                type: type,
                USER_NUM: id,
                ROLE_TYPE: "STATION",
                NOT_ADMIN: true,
                STA_NUM: $("#STA_NUM").val()
            },
            success: function(response) {
                var retour = $.parseJSON(response);
                var formDialog = $("<div id='formDialog'>");
                formDialog.html(retour.html);

                var title = type == "update" ? "Modification d'un g&eacute;rant" : "Cr&eacute;ation d'un g&eacute;rant";

                formDialog.dialog({
                    title: title,
                    modal: true,
                    resizable: false,
                    closeOnEscape: false,
                    width: 510,
                    buttons: {
                        Enregistrer: function() {
                            gerant.checkForm();
                        },
                        Fermer: function() {
                            $(this).dialog("destroy").remove();
                        }
                    },
                    open: function() {
                        // On masque les champs inutiles
                        $(".fieldDiv").hide();

                        // On ajoute un bouton Supprimer si on modifie la fiche user
                        if(type == "update") {
                            $(this).dialog("option", "buttons", {
                                Enregistrer: function() {
                                    gerant.checkForm();
                                },
                                Fermer: function() {
                                    $(this).dialog("destroy").remove();
                                },
                                Supprimer: function() {
                                    gerant.getFormDelete(id, "STATION");
                                }
                            });
                        }

                        if(type == "add") {
                            // On bloque le champ "Enregistrer"
                            $("button.ui-button:contains(Enregistrer)")
                                .attr("disabled", "disabled")
                                .attr("title", "Vous devez v&eacute;rifier que l'adresse mail n'est pas d&eacute;j&agrave; utilis&eacute; &agrave; l'aide du lien")
                                .css({
                                    opacity: 0.7,
                                    cursor: "default"
                                });

                            $("#USER_MAIL").change(function() {
                                $("#checkMail").hide();
                                $("#verifMail").css("color", "red");
                                $("button.ui-button:contains(Enregistrer)")
                                    .attr("disabled", "disabled")
                                    .attr("title", "Vous devez v&eacute;rifier que l'adresse mail n'est pas d&eacute;j&agrave; utilis&eacute; &agrave; l'aide du lien")
                                    .css({
                                        opacity: 0.7,
                                        cursor: "default"
                                    });
                            });
                        }

                        // Vérifier l'unicité du mail
                        $("#verifMail").click(function() {
                            gerant.checkUniqueEmail();
                        });
                    }
                });
            }
        });
    },

    checkEmailFormat: function() {
        var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var mail = $("#USER_MAIL").val();

        if(mail.trim() == "" || !regex.test(mail))
            return false;

        if(mail.toLowerCase().indexOf("@resmail.fr") != -1) {
            alert("Impossible de saisir une adresse mail \"xxx@resmail.fr\", vous devez saisir l'adresse mail personnel du g&eacute;rant.");
            return false;
        }

        return true;
    },

    checkUniqueEmail: function() {
        if(!gerant.checkEmailFormat()) return;

        $("#spinnerMail").show();
        $("#checkMail").hide();
        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "checkUniqueEmail",
                username: $("#USER_MAIL").val(),
                role: "STATION",
                STA_NUM: $("#STA_NUM").val()
            },
            success: function(response) {
                $("#spinnerMail").hide();

                var response = $.parseJSON(response);

                if (!response.data) {
                    $("#actionOnLockers").val("add");
                    $("#checkMail").show();
                    $("#verifMail").css("color", "green");
                    $("button.ui-button:contains(Enregistrer)")
                        .removeAttr("disabled", "disabled")
                        .removeAttr("title")
                        .css({
                            opacity: 1,
                            cursor: "pointer"
                        });
                } else if (response.data && response.data.onIo) {
                    var infoDialog = $("<div>");
                    infoDialog.append(
                        $("<p>Cette adresse email est d&eacute;j&agrave; existante sur cette station.</p>"),
                        $("<p>Vous devez utiliser une autre adresse email.</p>")
                    );

                    infoDialog.dialog({
                        title: "Email d&eacute;j&agrave; utilis&eacute;",
                        modal: true,
                        resizable: false,
                        buttons: {
                            OK: function() {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                } else if (response.data && response.data.onLockers) {
                    var nom = response.data.lastName;
                    var prenom = response.data.firstName;
                    var mail = response.data.username;
                    var lockersId = response.data.id;

                    // Cas d'un gérant déj&agrave; existant dans IO (nouvelle station)
                    if(response.data.gerantUpdate == 1) {
                        $("#actionOnMyreport").val("addUpdate");
                        $("#USER_NUM").val(response.data.USER_NUM);
                    }

                    var infoDialog = $("<div>");
                    infoDialog.append(
                        $("<p>Cette adresse email est d&eacute;j&agrave; existante sur le serveur d'authentification.</p>"),
                        $("<p>Celle-ci est associ&eacute;e &agrave; la personne suivante :</p>"),
                        $("<ul><li>Nom : " + nom + "</li><li>Pr&eacute;nom : " + prenom + "</li></ul>"),
                        $("<p>S'il s'agit de la m&ecirc;me personne, veuillez cliquer sur oui et compl&eacute;ter le formulaire.<br>Sinon, vous devez changer d'adresse email.</p>")
                    );

                    infoDialog.dialog({
                        title: "Email d&eacute;j&agrave; utilis&eacute;",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Oui: function() {
                                $(this).dialog("destroy").remove();
                                $("#checkMail").show();
                                $("#actionOnLockers").val("update");
                                $("button.ui-button:contains(Enregistrer)")
                                    .removeAttr("disabled", "disabled")
                                    .css({
                                        opacity: 1,
                                        cursor: "pointer"
                                    });
                                $("#USER_MAIL_CONF").val(mail);
                                $("#USER_NOM").val(nom);
                                $("#USER_PRENOM").val(prenom);
                                $("#USER_LOCKERS_ID").val(lockersId);
                            },
                            Non: function() {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                }
            }
        });
    },

    checkForm: function() {
        var error = false;
        var ul = $("<ul>");

        var mail = $("#USER_MAIL").val();
        var conf = $("#USER_MAIL_CONF").val();
        var nom = $("#USER_NOM").val();
        var prenom = $("#USER_PRENOM").val();
        var role = $("#ROLE_TYPE").val();
        var CAB_NUM = $("#CAB_NUM").val();

        if(!gerant.checkEmailFormat()) {
            error = true;
            ul.append($("<li>L'email n'est pas au bon format</li>"));
        }

        if(mail.trim() != conf.trim()) {
            error = true;
            ul.append($("<li>Les deux emails sont diff&eacute;rents</li>"));
            $("#errorMail").show();
        }

        if(nom.trim() == "") {
            error = true;
            ul.append($("<li>Le nom n'est pas rempli</li>"));
            $("#errorNom").show();
        }

        if(prenom.trim() == "") {
            error = true;
            ul.append($("<li>Le pr&eacute;nom n'est pas rempli</li>"));
            $("#errorPrenom").show();
        }

        if(role == "COMPTABLE" && CAB_NUM == "") {
            error = true;
            ul.append($("<li>Vous n'avez pas choisi de cabinet comptable</li>"));
            $("#errorCabinet").show();
        }
        
        if (error) {
            var errorDialog = $("<div>");
            errorDialog.append(
                $("<p>Le formulaire est incomplet ou comporte des erreurs :</p>"),
                ul
            );
            errorDialog.dialog({
                title: "Erreur dans le formulaire",
                modal: true,
                resizable: false,
                buttons: {
                    Fermer: function() {
                        $(this).dialog("destroy").remove();
                    }
                }
            });

            return;
        }

        gerant.saveUser();
    },

    saveUser: function() {
        var disabled = $("#formUser").find(":input:disabled").removeAttr("disabled");
        var formDatas = $("#formUser").serializeObject();
        disabled.attr("disabled", "disabled");
        
        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "saveUser",
                datas: formDatas
            },
            success: function(response) {
                var response = $.parseJSON(response);

                if(response.error) {
                    var errorDialog = $("<div>");
                    errorDialog.html("<center>" + response.error.message + "</center>");
                    errorDialog.dialog({
                        title: "Une erreur s'est produite",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Fermer: function() {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                } else if (response.data) {
                    $("#formDialog").dialog("destroy").remove();
                    var infoDialog = $("<div>");
                    infoDialog.html("<center>" + response.data.information + "</center>");
                    infoDialog.dialog({
                        title: "Enregistrement r&eacute;ussi",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Fermer: function() {
                                $(this).dialog("destroy").remove();
                                gerant.loadListe();
                            }
                        }
                    });
                }
            }
        });
    },

    getFormDelete: function(id, role) {
        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getFormDelete",
                USER_NUM: id,
                ROLE_TYPE: role
            },
            success: function(response) {
                var response = $.parseJSON(response);
                
                var deleteDialog = $("<div>");
                deleteDialog.html(response.html);

                deleteDialog.dialog({
                    title: "Confirmation de suppression",
                    modal: true,
                    resizable: false,
                    width: 500,
                    buttons: {
                        Oui: function() {
                            var formDatas = $("#formDeleteUser").serializeObject();
                            $(this).dialog("destroy").remove();
                            gerant.deleteUser(formDatas);
                        },
                        Non: function() {
                            $(this).dialog("destroy").remove();
                        }
                    },
                    close: function() {
                        $(this).dialog("destroy").remove();
                    }
                });
            }
        });
    },

    deleteUser: function(formDatas) {
        $.ajax({
            url: "../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "deleteUser",
                datas: formDatas
            },
            success: function(response) {
                var response = $.parseJSON(response);

                if(response.error) {
                    var errorDialog = $("<div>");
                    errorDialog.html("<center>" + response.error.message + "</center>");
                    errorDialog.dialog({
                        title: "Erreur de suppression",
                        width: 500,
                        buttons: {
                            Fermer: function() {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                } else if (response.data) {
                    $("#formDialog").dialog("destroy").remove();
                    var infoDialog = $("<div>");
                    infoDialog.html("<center>" + response.data.information + "</center>");
                    infoDialog.dialog({
                        title: "Suppression r&eacute;ussie",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Fermer: function() {
                                $(this).dialog("destroy").remove();
                                $("#gerant").attr("data-type", "add");
                                gerant.loadListe();
                            }
                        }
                    });
                }
            }
        });
    }
}

function EnableDisable_Cluster()
{
    if($("#STA_NUM_CLUSTER_MAITRE").attr("checked"))
    {
        $("#STA_NUM_CLUSTER_PARENT").attr("value","");
        $("#STA_NUM_CLUSTER_PARENT").attr("disabled",1);

    }
    else
    {
        $("#STA_NUM_CLUSTER_PARENT").attr("disabled",0);
    }

    
}
