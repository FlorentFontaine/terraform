var formstationsclose = true;

if (typeof (window[CRR]) == "undefined") {
    var CRR = 0;
}

function getStations(Etude) {
    if (!formstationsclose) {
        return;
    }

    $.ajax({
        url: '../BenchMark/ListeStationsAjax.php?notselect=1&etude=' + Etude + '&CRR=' + CRR,
        success: function (data) {

            $("<div>").attr("id", "dialog_list_station").html(data).dialog({
                title: "Liste des PDV",
                width: 700,
                height: $(window).height() - 250,
                modal: true,
                resizable: false,
                buttons: {
                    "Fermer": function () {
                        $("#dialog_list_station").dialog("destroy");
                    }
                },
                destroy: function () {
                    $(this).remove();
                }
            });
        }
    });
}