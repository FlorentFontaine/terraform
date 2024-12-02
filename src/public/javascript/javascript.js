
//    -----------------------------------
//                 LOADER
//    -----------------------------------

const loader = $("<div>").html('<svg class="loader" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 340 340">' +
    ' <circle cx="170" cy="170" r="160" stroke="#2799ff"/>' +
    ' <circle cx="170" cy="170" r="135" stroke="#0F67BF"/>' +
    ' <circle cx="170" cy="170" r="110" stroke="#2799ff"/>' +
    ' <circle cx="170" cy="170" r="85" stroke="#0F67BF"/></svg>');

function showLoader() {
    loader.dialog({
        height: "auto",
        width: "auto",
        closeText: "hide",
        modal: true,
        dialogClass: "no-titlebar"
    });
}

function hideLoader() {
    loader.dialog('close');
}

$(document).on( "ajaxStart", function () {
    showLoader();
});

$(document).on( "ajaxStop", function () {
    hideLoader();
});

$(document).on( "ajaxError", function () {
    hideLoader();
});

$(window).on("beforeunload", function (e) {

    const href = $(e.target.activeElement).attr("href");

    if (href && (href.toLowerCase().indexOf("mailto:") === 0 || href.toLowerCase().indexOf(".zip") !== -1)) {
        return;
    }

    showLoader();
});

// -----------------------------------------------


$(function () {
    $('.TitleMe').tooltip();
});

// Chargement du détail des postes de bilan via Tooltip

function loadTooltipPoste() {
    let element = $(".detail_tooltip");

    element.css({
        cursor: "help",
        color: "#7CB9E8",
        "padding-right": "10px",
        "font-weight": "bold",
        "font-size": "20px",
        "line-height": "0px",
    });

    element.on("click", function () {
        const poste = $(this).attr("data-id");
        const type = $(this).attr("data-type");
        const data = {};

        let params = new URL(document.location).searchParams;

        if (params && params.get("Produits")) {
            data.produits = params.get("Produits");
        }


        $.ajax({
            url: "/detail/" + type + "/poste/" + poste + "",
            //url: '../BilanDetail/Liste.php',
            type: 'post',
            data: data,
            success: function (response) {
                // Create a jQuery dialog with the response as content using the method customAlert in this file
                customAlert(
                    "D\u00e9tail du poste de " + type,
                    response,
                    function () {
                        // On click on the button OK, the dialog is destroyed
                        $(this).dialog("destroy");
                    },
                    "OK"
                );
            },
        });
    });
}

// ------------------------------------------------------

function changeClasse(classe, correction) {
    if (correction) {
        document.getElementById("correctionBal").value = 1;
        document.getElementById("classe").value = classe;
        document.getElementById("changeClasse").click();
    }
}

// ------------------------------------------------------

function customAlert(titre, message, fonction, buttons) {
    let MyButtons = {
        Ok: function () {
            $(this).dialog("close");
        },
    };

    if (buttons === "YESNO") {
        MyButtons.Annuler = function () {
            $(this).dialog("destroy");
        };
    }

    $("<div>").html(message).dialog({
        // resizable: false,
        // draggable: false,
        height: "auto",
        width: "auto",
        closeText: "hide",
        modal: true,
        title: titre,
        buttons: MyButtons,
        close: fonction,
        create: function () {
            $(this).css("maxHeight", 300);
            $(this).css("maxWidth", 500);
            $(this).css("minWidth", 250);
        }
    });

    return false;
}

function customConfirm(mes, fn) {
    $("<div>")
        .html(mes)
        .dialog({
            height: "auto",
            width: "auto",
            resizable: false,
            draggable: false,
            closeText: "hide",
            modal: true,
            title: "Confirmation",
            buttons: {
                OK: function () {
                    $(this).dialog("close");
                },
                Retour: function () {
                    $(this).dialog("destroy");
                },
            },
            close: fn,
            create: function () {
                $(this).css("maxHeight", 300);
                $(this).css("maxWidth", 500);
                $(this).css("minWidth", 250);
            }
        });
}
