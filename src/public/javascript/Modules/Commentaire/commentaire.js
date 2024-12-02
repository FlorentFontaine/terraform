
const commentaire = {

	// Fonction de récupération des commentaires
	load: function () {
		$(".commentaires").click(function () {
			const code = $(this).attr("data-code");
			const type = $(this).attr("data-type");
			const id = $(this).attr("data-id");
			const title = $(this).html();
			const childCommented = $(this).attr("data-child-commented");
			const url = "/commentaire/";
			const section = "table";

			// Récupération du formulaire d'ajout de commentaires pour le tableau
			commentaire.getCommentaire(id, code, type, title, url, section, childCommented);
		});

		$(".commentaires-libre").click(function () {
			const type = $(this).attr("data-type");
			const code = $(this).attr("data-code");
			const id = $(this).attr("data-id");
			const title = $(this).attr("data-title");
			const url = "/commentaire/section/"+type+"/show/";
			const section = "libre";

			// Récupération du formulaire d'ajout de commentaires pour le tableau
			commentaire.getCommentaire(id, code, type, title, url, section);
		});
	},

	// Fonction de récupération du formulaire d'ajout de commentaires
	getCommentaire: function (id, code, type, title, url, section, childCommented) {
		const commentaire_dialog = $("#commentaires");

		let buttons = [];

		if (id != "new" || childCommented == "yes") {
			buttons = [{
				text: "Supprimer tous les commentaires",
				class: "btn-left",
				click: function () {
					commentaire.deleteCommentaire(id, code, url);
				},
			}];
		}

		buttons.push({
			text: "Enregistrer",
			click : function () {
				commentaire.setCommentaire(code, url, section);
				$(this).dialog("close");
			}
		});

		buttons.push({
			text: "Fermer",
			click : function () {
				$(this).dialog("close");
			}
		});


		$.ajax({
			url: url + id,
			type: "GET",
			data: {
				code: code,
				type: type
			},
			success: function (retour) {
				commentaire_dialog.html(retour);
				commentaire_dialog.dialog({
					title: "Ajouter des commentaires : " + title,
					width: "950",
					height: "auto",
					maxHeight: "550",
					minHeight: "300",
					resizable: false,
					modal: true,
					dialogClass: "popup_commentaires",
					buttons: buttons,
					open: function () {
						// mettre le focus à la fin de la ligne
						$(".note-editable:first").focus();
					},
					close: function () {
						$(this).dialog("close");
					},
				});
			},
			error: function (retour) {
				console.log(retour);
				commentaire_dialog
					.html("Une erreur est survenue.<br />Merci d'essayer ult&eacute;rieurement.")
					.dialog({
						title: "Information",
						width: "250",
						height: "150",
						maxHeight: "550",
						minHeight: "300",
						resizable: false,
						modal: true,
						dialogClass: "popup_commentaires",
						buttons:{
							"Fermer": function(){
								$(this).dialog("close");
							}
						},
						close: function () {
							$(this).dialog("close");
						},
					});
			},
		});
	},

	// fonction d'ajout de commentaires
	setCommentaire: function (codePoste, url, section) {
		let actionRealisee = [];
		// Récupération des données du formulaire
		$('.summernote').each(function () {
			const commentaireContent = $(this).summernote('code');
			const type = $(this).siblings("input[type='hidden'][name='type']").val();
			const code = $(this).siblings("input[type='hidden'][name='code']").val();
			const id = $(this).siblings("input[type='hidden'][name='id']").val();
			const title = $(this).siblings("input[type='text'][name='title']").val();

			let data = {};

			if(section == "table") {
				const field = commentaire.getFieldNameByType(type);
				data = {
					CMT_COMMENTAIRE: commentaireContent,
					CMT_TYPE: type
				};
				data[field] = code;
			} else if (section == "libre") {
				data = {
					CML_COMMENTAIRE: commentaireContent,
					CML_INTITULE: title,
					CMS_ID: codePoste
				};
			}

			let commentaireVide = commentaireContent.replace(/(<([^>]+)>|\s|&nbsp;|\s*\r?\n\s*|\s*\t\s*)/gi, "") == "";

			if (!commentaireVide || id != "new") {
				$.ajax({
					url: url + id,
					type: commentaireVide ? "DELETE" : "POST",
					async: false,
					data: data,
					success: function (retour) {
						const response = JSON.parse(retour);

						if(section == "libre" || $('#module-commentaire').length > 0) {
							window.location.reload();
						}

						if(codePoste == code) {
							$("span[data-code='"+code+"']").attr("data-id", response.id);
						} else {
							actionRealisee.push(response.action);
						}
					},
					error: function (retour) {
						customAlert("Information","Une erreur s'est produite lors de l'ajout du commentaire");
					},
				});
			}
		});

		if(actionRealisee.includes("new") || actionRealisee.includes("update")) {
			$("span[data-code='"+codePoste+"']").attr("data-child-commented", "yes");
		} else {
			$("span[data-code='"+codePoste+"']").attr("data-child-commented", "no");
		}
	},

	// Fonction de suppression de commentaires
	deleteCommentaire: function (id, code, url) {

		const childCommented = [];

		if(id != "new") {
			childCommented.push(id);
		}

		$("#commentaires").find("input[type='hidden'][name='id'][value!='new']").each(function () {
			const childId = $(this).val();
			childCommented.push(childId);
		});

		$("<div>").dialog({
			resizable: false,
			height: "auto",
			width: 400,
			modal: true,
			title: "Supprimer un commentaire",
			open: function () {
				$(this).html("<p>Etes-vous sur de vouloir supprimer ces commentaires ?</p>");
			},
			buttons: {
				"Annuler": function () {
					$(this).dialog("close");
				},
				"Supprimer": function () {
					childCommented.forEach(function (id) {
						$.ajax({
							url: url + id,
							type: "DELETE",
							success: function (retour) {
								let divCML = $("div[id='CML"+id+"']"); 
								if(divCML) {
									divCML.remove();
								}
								$("span[data-id='"+id+"']").attr("data-id", "new");
								$("span[data-code='"+code+"']").attr("data-id", "new");
								$("span[data-code='"+code+"']").attr("data-child-commented", "no");
							},
							error: function (retour) {
								customAlert("Information", "Une erreur s'est produite lors de la suppression du commentaire");
							},
						});
					});
					$(this).dialog("close");
					$("#commentaires").dialog("close");
				}
			}
		});
	},

	getFieldNameByType: function(type) {
        let field = '';
        switch (type) {
            case 'bilan':
                field = 'CPB_NUM';
                break;
            case 'compte':
                field = 'code_compte';
                break;
            case 'poste':
                field = 'codePoste';
                break;
            default:
                break;
        }

        return field;
    }
};
