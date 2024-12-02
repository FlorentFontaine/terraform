let debug = {
    init: function () {

        // ---------------------- TABLES

            $("td").each(function () {
                if ($(this).text().toLowerCase() == "string") {
                    $(this).css("color", "red");
                } else if ($(this).text().toLowerCase() == "int" || $(this).text().toLowerCase() == "integer") {
                    $(this).css("color", "green");
                } else if ($(this).text().toLowerCase() == "null" || $(this).text() == "NULL") {
                    $(this).css("color", "blue");
                } else if ($(this).text().toLowerCase() == "float") {
                    $(this).css("color", "yellow");
                } else if ($(this).text().toLowerCase() == "boolean") {
                    $(this).css("color", "purple");
                } else if ($(this).text().toLowerCase() == "array") {
                    $(this).css("color", "orange");
                } else if ($(this).text().toLowerCase() == "object") {
                    $(this).css("color", "brown");
                }
            });

            $(".table-sort th").click(function () {
                var $table = $(this).closest("table");
                var $tbody = $table.find("tbody");
                var column = $(this).index();
                var order = $(this).data("sort");

                // Mettez à jour les icônes pour les autres colonnes
                $table.find("th").each(function () {
                    if (this !== $(this).get(column)) {
                        $(this).data("sort", "asc");
                        $(this).find("i").removeClass("fa-sort-asc fa-sort-desc").addClass("fa-sort");
                    }
                });

                // Mettez à jour l'icône de tri de la colonne actuelle
                if (order === "asc") {
                    $(this).data("sort", "desc");
                    $(this).find("i").removeClass("fa-sort").addClass("fa-sort-desc");
                } else {
                    $(this).data("sort", "asc");
                    $(this).find("i").removeClass("fa-sort-desc").addClass("fa-sort-asc");
                }

                // Triez le tableau en fonction de la colonne
                var rows = $tbody.find("tr").get();
                rows.sort(function (a, b) {
                    var keyA = $(a).find("td").eq(column).text().toLowerCase();
                    var keyB = $(b).find("td").eq(column).text().toLowerCase();
                    if($(a).find("td").eq(column).hasClass("chiffre") && $(b).find("td").eq(column).hasClass("chiffre")) {
                        if (order === "asc") {
                            return keyA - keyB;
                        } else {
                            return keyB - keyA;
                        }
                    } else {
                        if (order === "asc") {
                            return keyA.localeCompare(keyB);
                        } else {
                            return keyB.localeCompare(keyA);
                        }
                    }
                });

                $tbody.empty().append(rows);
            });

        // ---------------------- ONGLETS

            // Au chargement de la page, masquez tous les onglets sauf le premier
            $('.tab-pane').not(':first').hide();
            $('.nav-link-tab').on('click', function (e) {
                e.preventDefault();

                // Désactive la classe "active" pour tous les liens de navigation
                $('.nav-link-tab').removeClass('active');

                // Masquez tous les onglets
                $('.tab-pane').hide();

                // Récupérez l'ID de l'onglet correspondant
                var tabId = $(this).attr('data');

                // Affichez l'onglet correspondant
                $(tabId).show();

                // Activez le lien de navigation actuel
                $(this).addClass('active');
            });

            // Si aucune tab active on prend la premiere par defaut
            if($('.nav-link-tab.active').length == 0) {
                $('.nav-link-tab').first().addClass('active');
                $('.tab-pane').first().show();
            }
    }
};