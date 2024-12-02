var utilisateurs = {
    init: function () {
        // Par défaut, on masque les filtres Cabinets
        $(".filterCabinet").css("display", "none");

        // Initialisation de la liste
        $su.initListe($("#listContainer"));

        // Chargement de la liste des utilisateurs
        utilisateurs.loadListe();

        // Changement sur l'un des filtres
        $("#ROLE, #ORDER").change(function () {
            // Affichage des filtres Cabinets si on choisit d'afficher seulement les cabinets
            if ($("#ROLE").val() === "COMPTABLE") {
                $(".filterCabinet").css("display", "block");
            } else if ($("#ROLE").val() !== "COMPTABLE") {
                if ($("#ORDER").val() === "CAB_NOM_ASC" || $("#ORDER").val() === "CAB_NOM_DESC") {
                    $("#ORDER").val("USER_NOM_ASC");
                }

                $(".filterCabinet").css("display", "none");
            }

            utilisateurs.loadListe();
        });

        // On tape dans le champ de recherche d'un utilisateur
        var wait;
        $("#USER").keyup(function () {
            if (wait) {
                clearTimeout(wait);
            }
            wait = setTimeout(function () {
                utilisateurs.loadListe();
            }, 500);
        });

        // Click pour ajouter un utilisateur
        $("#addUser").click(function () {
            utilisateurs.getForm("add", null);
        });
    },

    loadListe: function (page) {
        var divListe = $("#listContainer");
        var tabdatas = divListe.find(".tabdatas");
        var tbody = divListe.find("tbody");
        tabdatas.unbind("scroll");

        var page = parseInt(page) || 1;

        if (page === 1) {
            tbody.empty();
            $su.tableLoading(tbody, true);
        }

        var type = $("#ROLE").val();
        var order = $("#ORDER").val();
        var user = $("#USER").val();

        // Ajax pour récupérer les utilisateurs
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getUsers",
                page: page,
                ROLE_TYPE: type,
                ORDER: order,
                USER: user
            },
            success: function (response) {
                $su.tableLoading(tbody, false);

                if (page === 1) {
                    tbody.empty();
                }

                var response = $.parseJSON(response);

                $(".tabresults #nb")
                    .css({fontWeight: "bold"})
                    .html(response.results);

                if (!response.data) {
                    $su.tableNoResults(tbody);
                    return;
                }

                $.each(response.data, function (o, obj) {
                    var tr = $("<tr>");

                    if (o % 2 !== 0) {
                        tr.addClass("impaire");
                    }

                    tr.append(
                        $("<td>").html(obj.USER_NOM),
                        $("<td>").html(obj.USER_PRENOM),
                        $("<td>").html(obj.USER_MAIL),
                        $("<td>")
                            .css("text-align", "center")
                            .html(obj.ROLE_TYPE),
                        $("<td>").html(obj.CAB_NOM ? obj.CAB_NOM : ""),
                        $("<td>")
                            .css("text-align", "center")
                            .html(obj.CC_IS_ADMIN && obj.CC_IS_ADMIN === 1 ? "X" : ""),
                        $("<td>")
                            .css("text-align", "center")
                            .append(
                                $("<a>")
                                    .css("font-size", "10px")
                                    .attr("href", "../../login/login.php?loginAs=1&lockersId=" + obj.USER_LOCKERS_ID + "&roleNum=" + obj.ROLE_NUM)
                                    .html("Se connecter")
                            ),
                        $("<td>")
                            .css("text-align", "center")
                            .append(
                                $("<a>")
                                    .attr("href", "#")
                                    .css("font-size", "10px")
                                    .html("Modifier")
                                    .click(function () {
                                        utilisateurs.getForm("update", obj.USER_NUM, obj.ROLE_TYPE);
                                    })
                            )
                    );

                    if (obj.ROLE_TYPE !== "SIEGE" && obj.ROLE_TYPE !== "CDV") {
                        tr.append(
                            $("<td>")
                                .css("text-align", "center")
                                .append(
                                    $("<a>")
                                        .attr("href", "#")
                                        .css("font-size", "10px")
                                        .html("R&eacute;affecter les dossiers")
                                        .click(function () {
                                            utilisateurs.getFormReaffectDossiers(obj.USER_NUM, obj.ROLE_TYPE);
                                        })
                                )
                        );
                    } else {
                        tr.append(
                            $("<td>")
                        );
                    }

                    if (page === 1 && o === 0) {
                        $su.setColumnsSize(divListe, tr);
                    }

                    tbody.append(tr);
                });

                if (page === 1) {
                    tabdatas.scrollTop(0).animate({scrollTop: 0});
                }

                if (page < response.page) {
                    tabdatas.bind("scroll", function () {
                        if (
                            $(this).scrollTop() + $(this).height() >=
                            $(this)[0].scrollHeight * 0.9
                        ) {
                            $su.tableLoading(tbody, true);

                            utilisateurs.loadListe(page + 1);
                            tabdatas.unbind("scroll");
                        }
                    });

                    if (
                        tabdatas.scrollTop() + tabdatas.height() ===
                        tabdatas[0].scrollHeight
                    )
                        tabdatas.trigger("scroll");
                }
            }
        });
    },

    getForm: function (type, id, role) {
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getForm",
                type: type,
                USER_NUM: id,
                ROLE_TYPE: role
            },
            success: function (response) {
                var response = $.parseJSON(response);

                var formDialog = $("<div id='formDialog'>");

                formDialog.html(response.html);

                var title = type === "update" ? "Modification d'un utilisateur" : "Ajout d'un utilisateur";

                formDialog.dialog({
                    title: title,
                    modal: true,
                    resizable: false,
                    width: 510,
                    buttons: {
                        Fermer: function () {
                            $(this).dialog("destroy").remove();
                        },
                        Enregistrer: function () {
                            utilisateurs.checkForm();
                        }
                    },
                    open: function () {
                        // On ajoute un bouton Supprimer si on modifie la fiche user
                        if (type === "update") {
                            $(this).dialog("option", "buttons", {
                                Supprimer: function () {
                                    utilisateurs.getFormDelete(id, role);
                                },
                                Fermer: function () {
                                    $(this).dialog("destroy").remove();
                                },
                                Enregistrer: function () {
                                    utilisateurs.checkForm();
                                }
                            });
                        }

                        if (type === "add") {
                            // On bloque le champ "Enregistrer"
                            $("button.ui-button:contains(Enregistrer)")
                                .attr("disabled", "disabled")
                                .attr("title", "Vous devez v&eacute;rifier que l'adresse mail n'est pas d&eacute;j&agrave; utilis&eacute; &agrave; l'aide du lien")
                                .css({
                                    opacity: 0.7,
                                    cursor: "default"
                                });

                            $("#USER_MAIL").change(function () {
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

                        // On masque les champs inutiles
                        $(".fieldDiv").hide();

                        // Si on change de type, on affiche les champs si besoin
                        $("#ROLE_TYPE").change(function () {
                            $(".fieldDiv").hide();
                            if ($(this).val() === "COMPTABLE") {
                                $(".fieldCPT").show();
                            }
                            if ($(this).val() === "SIEGE") {
                                $(".fieldSIE").show();
                            }
                            if ($(this).val() === "CDR") {
                                $(".fieldCDR").show();
                            }
                            if ($(this).val() === "CDS") {
                                $(".fieldCDS").show();
                            }
                            if ($(this).val() === "CDV") {
                                $(".fieldCDV").show();
                            }
                        });

                        $("#ROLE_TYPE").trigger("change");

                        // Vérifier l'unicité du mail
                        $("#verifMail").click(function () {
                            utilisateurs.checkUniqueEmail();
                        });
                    }
                });
            }
        });
    },

    checkUniqueEmail: function () {
        if (!utilisateurs.checkEmailFormat()) {
            return;
        }

        $("#spinnerMail").show();
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "checkUniqueEmail",
                username: $("#USER_MAIL").val()
            },
            success: function (response) {
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
                } else if (response.data && response.data.onLockers) {
                    var nom = response.data.lastName;
                    var prenom = response.data.firstName;
                    var mail = response.data.username;
                    var lockersId = response.data.id;

                    var infoDialog = $("<div>");
                    infoDialog.append(
                        $("<p>Cette adresse email est d&eacute;j&agrave; existante sur le serveur d'authentification.</p>"),
                        $("<p>Celle-ci est associ&eacute;e &agrave; la personne suivante :</p>"),
                        $("<ul><li>Nom : " + nom + "</li><li>Pr&eacute;nom : " + prenom + "</li></ul>"),
                        $("<p>S'il s'agit de la m&ecirc;me personne, veuillez cliquer sur oui et remplir le formulaire.<br>Sinon, vous devez changer d'adresse email.</p>")
                    );

                    // Cas d'un gérant déj&agrave; existant dans Comète (nouvelle station)
                    if (response.data.userUpdate === 1) {
                        $("#actionOnMyreport").val("addUpdate");
                        $("#USER_NUM").val(response.data.USER_NUM);
                    }

                    infoDialog.dialog({
                        title: "Email d&eacute;j&agrave; utilis&eacute;",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Oui: function () {
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
                            Non: function () {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                }
            }
        });
    },

    checkEmailFormat: function () {
        var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        var mail = $("#USER_MAIL").val();

        return !(mail.trim() === "" || !regex.test(mail));
    },

    checkForm: function () {
        $("#errorMail, #errorNom, #errorPrenom, #errorCabinet").hide();
        var error = false;
        var ul = $("<ul>");

        var mail = $("#USER_MAIL").val();
        var conf = $("#USER_MAIL_CONF").val();
        var nom = $("#USER_NOM").val();
        var prenom = $("#USER_PRENOM").val();
        var role = $("#ROLE_TYPE").val();
        var cabinet = $("#CAB_NUM").val();

        if (!utilisateurs.checkEmailFormat()) {
            error = true;
            ul.append($("<li>L'email n'est pas au bon format</li>"));
        }

        if (mail.trim() !== conf.trim()) {
            error = true;
            ul.append($("<li>Les deux emails sont diff&eacute;rents</li>"));
            $("#errorMail").show();
        }

        if (nom.trim() === "") {
            error = true;
            ul.append($("<li>Le nom n'est pas rempli</li>"));
            $("#errorNom").show();
        }

        if (prenom.trim() === "") {
            error = true;
            ul.append($("<li>Le pr&eacute;nom n'est pas rempli</li>"));
            $("#errorPrenom").show();
        }

        if (role === "COMPTABLE" && cabinet === "") {
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
                    Fermer: function () {
                        $(this).dialog("destroy").remove();
                    }
                }
            });

            return;
        }

        utilisateurs.saveUser();
    },

    saveUser: function () {
        var disabled = $("#formUser").find(":input:disabled").removeAttr("disabled");
        var formDatas = $("#formUser").serializeObject();
        disabled.attr("disabled", "disabled");

        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "saveUser",
                datas: formDatas
            },
            success: function (response) {
                var response = $.parseJSON(response);

                if (response.error) {
                    var errorDialog = $("<div>");
                    errorDialog.html("<center>" + response.error.message + "</center>");
                    errorDialog.dialog({
                        title: "Une erreur s'est produite",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Fermer: function () {
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
                            Fermer: function () {
                                $(this).dialog("destroy").remove();
                                utilisateurs.loadListe();
                            }
                        }
                    });
                }
            }
        });
    },

    getFormDelete: function (id, role) {
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getFormDelete",
                USER_NUM: id,
                ROLE_TYPE: role
            },
            success: function (response) {
                var response = $.parseJSON(response);

                var deleteDialog = $("<div>");
                deleteDialog.html(response.html);

                deleteDialog.dialog({
                    title: "Confirmation de suppression",
                    modal: true,
                    resizable: false,
                    width: 500,
                    buttons: {
                        Non: function () {
                            $(this).dialog("destroy").remove();
                        },
                        Oui: function () {
                            var formDatas = $("#formDeleteUser").serializeObject();
                            $(this).dialog("destroy").remove();
                            utilisateurs.deleteUser(formDatas);
                        }
                    },
                    close: function () {
                        $(this).dialog("destroy").remove();
                    },
                    open: function () {
                        if (response.hasDossiers) {
                            // On bloque le champ "Enregistrer"
                            $("button.ui-button:contains(Oui)")
                                .attr("disabled", "disabled")
                                .css({
                                    opacity: 0.7,
                                    cursor: "default"
                                });

                            // On dévérouille champ "Oui", une fois qu'on a choisi une personne
                            $("#NEW_USER").change(function () {
                                $("button.ui-button:contains(Oui)")
                                    .removeAttr("disabled", "disabled")
                                    .css({
                                        opacity: 1,
                                        cursor: "pointer"
                                    });
                            });
                        }
                    }
                });
            }
        });
    },

    deleteUser: function (formDatas) {
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "deleteUser",
                datas: formDatas
            },
            success: function (response) {
                var response = $.parseJSON(response);

                if (response.error) {
                    var errorDialog = $("<div>");
                    errorDialog.html("<center>" + response.error.message + "</center>");
                    errorDialog.dialog({
                        title: "Erreur de suppression",
                        width: 500,
                        buttons: {
                            Fermer: function () {
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
                            Fermer: function () {
                                $(this).dialog("destroy").remove();
                                utilisateurs.loadListe();
                            }
                        }
                    });
                }
            }
        });
    },

    getFormReaffectDossiers: function (USER_NUM, ROLE_TYPE) {
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "getFormReaffectDossiers",
                USER_NUM: USER_NUM,
                ROLE_TYPE: ROLE_TYPE
            },
            success: function (response) {
                var response = $.parseJSON(response);

                var dossiersDialog = $("<div>");
                dossiersDialog.html(response.html);

                dossiersDialog.dialog({
                    title: "R&eacute;affectation de dossiers",
                    modal: true,
                    resizable: false,
                    width: 500,
                    close: function () {
                        $(this).dialog("destroy").remove();
                    },
                    open: function () {
                        if (response.hasDossiers === 1) {
                            $(this).dialog("option", "buttons", {
                                Fermer: function () {
                                    $(this).dialog("destroy").remove();
                                },
                                Valider: function () {
                                    var formDatas = $("#formReaffectDossiers").serializeObject();
                                    $(this).dialog("destroy").remove();
                                    utilisateurs.reaffectDossiers(formDatas);
                                }
                            });

                            // On bloque le champ "Valider"
                            $("button.ui-button:contains(Valider)")
                                .attr("disabled", "disabled")
                                .css({
                                    opacity: 0.7,
                                    cursor: "default"
                                });

                            // On dévérouille champ "Valider", une fois qu'on a choisi une personne
                            $("#NEW_USER").change(function () {
                                $("button.ui-button:contains(Valider)")
                                    .removeAttr("disabled", "disabled")
                                    .css({
                                        opacity: 1,
                                        cursor: "pointer"
                                    });
                            });
                        } else {
                            $(this).dialog("option", "buttons", {
                                Fermer: function () {
                                    $(this).dialog("destroy").remove();
                                }
                            });
                        }
                    }
                });
            }
        });
    },

    reaffectDossiers: function (formDatas) {
        $.ajax({
            url: "../../Utilisateurs/Utilisateurs.ajax.php",
            type: "POST",
            data: {
                action: "reaffectDossiers",
                datas: formDatas
            },
            success: function (response) {
                var response = $.parseJSON(response);

                if (response.error) {
                    var errorDialog = $("<div>");
                    errorDialog.html("<center>" + response.error.message + "</center>");
                    errorDialog.dialog({
                        title: "Erreur de r&eacute;affectation",
                        width: 500,
                        buttons: {
                            Fermer: function () {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                } else if (response.data) {
                    var infoDialog = $("<div>");
                    infoDialog.html("<center>" + response.data.information + "</center>");
                    infoDialog.dialog({
                        title: "R&eacute;affectation r&eacute;ussie",
                        modal: true,
                        resizable: false,
                        buttons: {
                            Fermer: function () {
                                $(this).dialog("destroy").remove();
                            }
                        }
                    });
                }
            }
        });
    }
}
