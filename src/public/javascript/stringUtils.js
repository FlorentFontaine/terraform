
var su = 
{
    // Affiche une dialog box de choix oui/non
    dialog_CONFIRM: function(title, msg, retFunc)
    {
        $("<div id='DBOX_CONFIRM'>")
        .html(msg)
        .dialog(
        {
            title: title,
            width: (msg.length < 50) ? 350 : 450,
            minheight: 200,
            create: function() { $(this).css("maxHeight", 500); },
            modal: true,
            resizable: false,
            buttons:{                
                "Oui": function() {
                    retFunc(true);
                    $(this).dialog("destroy").remove();
                },
                "Non": function() { $(this).dialog("close"); },
            },
            close: function() {
                retFunc(false);
                $(this).dialog("destroy").remove();
            }
        });
    },


    // Affiche une dialog box d'infos
    dialog_INFO: function(title, msg, retFunc)
    {
        $("<div id='DBOX_INFO'>")
        .html(msg)
        .dialog(
        {
            title: title,
            width: 500,
            minheight: 200,
            create: function() {
                $(this).css("maxHeight", 500);
            },
            modal: true,
            resizable: false,
            buttons:{
                "Fermer": function(){ $(this).dialog("close"); }
            },
            close: function(){
                $(this).dialog("destroy").remove();
                if (retFunc) retFunc();
            }
        });
    },
    
    
    // Affiche/cache une fenetre de chargement DBOX_LOAD
    dialog_LOAD: function(display)
    {
        if (display)
        {
            var $DBOX_LOAD = $("<div id='DBOX_LOAD'>").css("background-color", "#FFF");
            $DBOX_LOAD.html("<br/><br/><center><img src='../images/rouegrise.gif' /></center>");

            $DBOX_LOAD.dialog({
                title: 'Veuillez patienter ...',
                height: 'auto',
                width: '180',
                resizable: false,
                modal: true,
                closeOnEscape: false,
                open: function() {
                    $(this).parents(".ui-dialog").find(".ui-dialog-titlebar-close").hide();
                },
                close: function(){
                    $(this).dialog("destroy").remove();
                }
            });
        }
        else
        {
            $("#DBOX_LOAD").dialog('close');
        }
    },
    
    
   
    
}

var $su = {
    dateUS: function(date) {
      var d = date.split(" ");
  
      var tmp = d[0].split("/");
      tmp.reverse();
      d[0] = tmp.join("-");
  
      return d.join(" ");
    },
  
    dateFR: function(date) {
      var d = date.split(" ");
  
      var tmp = d[0].split("-");
      tmp.reverse();
      d[0] = tmp.join("/");
  
      return d.join(" ");
    },
  
    /**
     * Initialise une liste scrollable
     */
    initListe: function(listContainer) {
      var thead = listContainer.find(".tabhead table thead");
  
      // Ajout d'un <td> dans l'entête du tableau pour supprimer décalage de la scrollbar
      var scrollbarWidth = $su.getScrollbarWidth() - 1;
  
      if (scrollbarWidth) {
        var nbTr = thead.find("tr").length;
        thead.find("tr:first").append(
          $("<th>")
            .attr({ rowspan: nbTr })
            .addClass("thScrollbar")
            .css({ width: scrollbarWidth, padding: 0 })
        );
      }
  
      // Hauteur automatique de la liste
      // listContainer.find(".tabdatas").css({ height: $su.getHeightListe() });
    },
  
    getHeightListe: function() {
      var height = $("#corp").height();
      $("#corp > *")
        .not("#myunload")
        .not("script")
        .each(function() {
          height -= $(this).outerHeight(true);
        });

      return height;
    },
  
    getScrollbarWidth: function() {
      var outer = document.createElement("div");
      outer.style.visibility = "hidden";
      outer.style.width = "100px";
      outer.style.msOverflowStyle = "scrollbar";
  
      document.body.appendChild(outer);
  
      var widthNoScroll = outer.getBoundingClientRect().width;
      outer.style.overflow = "scroll";
  
      var inner = document.createElement("div");
      inner.style.width = "100%";
      outer.appendChild(inner);
  
      var widthWithScroll = inner.getBoundingClientRect().width;
  
      outer.parentNode.removeChild(outer);
  
      return widthNoScroll - widthWithScroll + 1;
    },
  
    tableLoading: function(tbody, show, nbCol) {
      if (show) {
        if (!nbCol) {
          nbCol = 0;
  
          var parent = tbody.parents(".tabdatas").length
            ? tbody.parents(".tabdatas").parent()
            : tbody.parent();
  
          parent
            .find("thead:visible")
            .find("tr:first")
            .find("th")
            .not("thScrollbar")
            .each(function() {
              nbCol += $(this).attr("colspan") ? $(this).attr("colspan") * 1 : 1;
            });
  
          tbody.append(
            $("<tr>")
              .addClass("trLoading")
              .append(
                $("<td>")
                  .attr({ colspan: nbCol })
                  .css({ textAlign: "center" })
                  .append(
                    $("<img>")
                      .attr({ src: "../../images/spinner.gif" })
                      .css({ width: "16px" })
                  )
              )
          );
        }
      } else {
        tbody.find(".trLoading").remove();
      }
    },
  
    tableNoResults: function(tbody, msg, nbCol) {
      tbody.find(".noResult").remove();
  
      if (!nbCol) {
        nbCol = 0;
  
        var parent = tbody.parents(".tabdatas").length
          ? tbody.parents(".tabdatas").parent()
          : tbody.parent();
  
        parent
          .find("thead:visible")
          .find("tr:first")
          .find("th")
          .not("thScrollbar")
          .each(function() {
            nbCol += $(this).attr("colspan") ? $(this).attr("colspan") * 1 : 1;
          });
  
        tbody.append(
          $("<tr>")
            .addClass("trLoading")
            .html(
              $("<td>")
                .attr({ colspan: nbCol })
                .css({ textAlign: "center" })
                .html(msg ? msg : "Pas de r&eacute;sultats")
            )
        );
      }
    },
  
    setColumnsSize: function(divliste, tr) {
      tr.find("td").each(function(i, td) {
        var tdHead = divliste.find(
          ".tabhead thead:visible tr:first th:not(thScrollbar):eq(" + i + ")"
        );
        var width = Math.round(tdHead.width());
        tdHead.css({ width: width + "px", maxWidth: width + "px" });
        $(td).css({ width: width + "px", maxWidth: width + "px" });
  
        if (tdHead.css("min-width")) {
          $(td).css({ minWidth: tdHead.css("min-width") });
        }
      });
    },
  
    capitalize: function(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    },
  
    NombreFR: function(nombre) {
      if (nombre != 0) {
        nombre = $.trim(nombre);
        nombre = new Intl.NumberFormat("fr-FR", {
          style: "decimal",
          maximumFractionDigits: 0
        }).format(nombre);
      } else {
        nombre = "0";
      }
  
      return nombre;
    }
  };
  
  $.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name]) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || "");
      } else {
        o[this.name] = this.value || "";
      }
    });
    return o;
  };
  


// Surcharge DatePicker - Nicolas DEROUET
$.fn.datepicker_adv = function(param)
{
    if($(this).length != 1)
        return;
    
    var chkDate = function(obj)
    {
        if (obj == undefined || typeof obj !== "object") obj = $(this);

        try {
            var v = obj.val();
            if (v.split("/").length == 2) v += "/"+gc_dateserver.getFullYear();
            var d = $.datepicker.parseDate("dd/mm/yy", v, null);
        } catch(e) {
            var d = gc_dateserver;
        }

        obj.val($.datepicker.formatDate('dd/mm/yy', d));
    };

    $(this).bind("blur", function()
    {
        var v = $(this).val();
        chkDate($(this));
        if (v !== $(this).val()) $(this).trigger("change");
    });

    $(this).bind("focus, click", function()
    {
        $(this).one('mouseup', function(ev){ ev.preventDefault(); $(this).select(); });
        $(this).unbind("keydown").bind("keydown", function (e)
        {
            if (e.keyCode===9 || e.keyCode===13)
            {
                chkDate($(this));
                $(this).select().datepicker("hide");
            }
        });

        if ($(this).is(":focus"))
            $(this).select().datepicker("show");
    });

    if (!param) param = { changeYear: true, changeMonth: true, maxDate: '+5y' };
    
    $(this).datepicker(param).datepicker($.datepicker.regional[ "fr" ]);

    return this;
};


// Affiche un loader dans $(this)
$.fn._loader = function(display)
{       
    var icon_loader = $("<img>").attr({id: "icon_loader", src: "../images/icon_loader.gif"})
        
    $(this).parent().find("#icon_loader").remove();
    var loader_height = 30;

    if (display)
    {
        var this_height = ($(this).height()) ? $(this).height() : 50;
        var margin = (this_height/2) - loader_height/2;
        $(this).before(icon_loader.css({ width: loader_height, height: loader_height, display: "block", margin: margin+"px auto" }));
        $(this).hide();
    }
    else
    {
        $(this).parent().find("#icon_loader").remove();
        $(this).show();
    }
}