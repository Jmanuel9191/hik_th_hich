/* ==========================================================================
   HUK · Editor de la página web del proyecto y de las plantillas (panel)
   Vistas: modules/admin/views/paginas-proyecto/*. Responde PaginasProyectoController (JSON {exito, mensaje, errores, id}).
   1. Abrir un formulario en el modal:  <button data-pe-abrir="URL">  (sección o bloque)
   2. Catálogo de bloques:              <button data-pe-catalogo="idseccion"> → [data-pe-tipo] abre el formulario
   3. Guardar el formulario del modal:  <form data-pe-form>  (con fotos: FormData)
   4. Botones de acción:                <button data-pe-accion="URL" [data-confirmar="¿…?"]>
   5. Filas de una lista:               [data-pe-lista] con [data-pe-filas], <template data-pe-plantilla>,
                                        [data-pe-fila-agregar], [data-pe-fila-mover], [data-pe-fila-quitar]
   6. Foto con vista previa:            [data-pe-imagen] con [data-pe-imagen-archivo], [data-pe-imagen-valor], [data-pe-imagen-quitar]
   7. Editor de texto simple (TinyMCE): <textarea data-pe-html>
   8. Plegar secciones y bloques:       [data-pe-plegar], "Plegar todo" (se recuerda en esta pestaña)
   9. Cargar plantilla / guardar como plantilla
   10. Después de guardar se recarga y vuelve al mismo lugar, con un destello dorado.
   11. Tabla:                           [data-pe-tabla] grilla editable + "Pegar desde Excel"; se guarda en JSON en [data-pe-tabla-valor]
   12. Subir varias fotos:              [data-pe-varias-archivos] crea una fila por foto en la lista
   13. Elegir fotos / videos / docs:    [data-pe-elegir] casillas con miniatura, "Marcar todos", contador
   14. Campos condicionales:            [data-pe-si="campo:valor1,valor2"] se ven solo con esa opción marcada
   ========================================================================== */
(function ($) {
  "use strict";

  var $editor = $("[data-pe]");
  if (!$editor.length) return;

  var $modal = $("#pe-modal");
  var $contenido = $modal.find("[data-pe-modal-contenido]");
  var seccionCatalogo = null;
  var CLAVE_IR = "huk-pe-ir";
  var CLAVE_PLEGADOS = "huk-pe-plegados";

  function csrf() {
    var d = {};
    d[$('meta[name="csrf-param"]').attr("content")] = $('meta[name="csrf-token"]').attr("content");
    return d;
  }
  function error(msg) {
    Swal.fire({ icon: "error", titleText: msg || "No se pudo completar." });
  }
  function guardarSesion(clave, valor) {
    try { sessionStorage.setItem(clave, JSON.stringify(valor)); } catch (e) { /* sin almacenamiento: no pasa nada */ }
  }
  function leerSesion(clave, porDefecto) {
    try { var v = sessionStorage.getItem(clave); return v ? JSON.parse(v) : porDefecto; } catch (e) { return porDefecto; }
  }

  /* 10. Recargar y volver al mismo lugar */
  function recargar(ir) {
    if (ir) guardarSesion(CLAVE_IR, ir);
    window.location.reload();
  }
  $(function () {
    var ir = leerSesion(CLAVE_IR, null);
    if (!ir) return;
    try { sessionStorage.removeItem(CLAVE_IR); } catch (e) { /* nada */ }
    var el = document.getElementById(ir);
    if (!el) return;
    el.scrollIntoView({ block: "center" });
    el.classList.add("is-destello");
    setTimeout(function () { el.classList.remove("is-destello"); }, 1800);
  });

  /* 1. Abrir formulario en el modal */
  function abrir(url) {
    $contenido.html('<div class="huk-pe-cargando"><i class="fa fa-spinner fa-spin"></i> Cargando…</div>');
    $modal.modal("show");
    $.get(url)
      .done(function (html) {
        $contenido.html(html);
        iniciarEditores($contenido);
        iniciarTablas($contenido);
        actualizarSi($contenido);
        $contenido.find("[data-pe-elegir]").each(function () { contarElegidos($(this).closest(".huk-pe-elegir")); });
        $contenido.find("input[type=text]:visible").first().trigger("focus");
      })
      .fail(function (x) {
        $modal.modal("hide");
        error(x.status === 403 ? "No tiene permiso para esto." : "No se pudo abrir el formulario.");
      });
  }
  $(document).on("click", "[data-pe-abrir]", function (e) {
    e.preventDefault();
    abrir($(this).data("pe-abrir"));
  });
  $modal.on("hidden.bs.modal", function () {
    quitarEditores($contenido);
    $contenido.empty();
  });

  /* 2. Catálogo */
  $(document).on("click", "[data-pe-catalogo]", function () {
    seccionCatalogo = $(this).data("pe-catalogo");
    $("#pe-catalogo").modal("show");
  });
  $(document).on("click", "[data-pe-tipo]", function () {
    var url = $(this).data("url") + (String($(this).data("url")).indexOf("?") > -1 ? "&" : "?") + "idseccion=" + encodeURIComponent(seccionCatalogo);
    $("#pe-catalogo").one("hidden.bs.modal", function () { abrir(url); }).modal("hide");
  });

  /* 3. Guardar el formulario del modal */
  $(document).on("submit", "form[data-pe-form]", function (e) {
    e.preventDefault();
    var form = this;
    var $f = $(form);
    if (window.tinymce) window.tinymce.triggerSave();
    $f.find("[data-pe-tabla]").each(function () { guardarTabla($(this)); });
    renumerar($f);
    var $btn = $f.find("[data-pe-guardar]");
    var texto = $btn.html();
    $btn.prop("disabled", true).html('<i class="fa fa-spinner fa-spin"></i> Guardando…');
    $f.find("[data-pe-errores]").prop("hidden", true).empty();
    $f.find(".has-error").removeClass("has-error");

    $.ajax({ url: form.action, method: "POST", data: new FormData(form), processData: false, contentType: false })
      .done(function (r) {
        if (r && r.exito) {
          $modal.modal("hide");
          var esBloque = form.action.indexOf("bloque-guardar") > -1;
          Swal.fire({ icon: "success", titleText: r.mensaje, timer: 1300, showConfirmButton: false })
            .then(function () { recargar(r.id ? (esBloque ? "pe-bloque-" : "pe-sec-") + r.id : null); });
          return;
        }
        var $err = $f.find("[data-pe-errores]");
        var lista = [];
        $.each((r && r.errores) || {}, function (campo, msg) {
          lista.push(msg);
          $f.find('[data-campo="' + campo + '"]').first().addClass("has-error");
        });
        if (!lista.length) lista.push((r && r.mensaje) || "No se pudo guardar.");
        $err.html("<ul><li>" + lista.map(function (m) { return $("<span>").text(m).html(); }).join("</li><li>") + "</li></ul>").prop("hidden", false);
        $f.closest(".modal").animate({ scrollTop: 0 }, 200);
      })
      .fail(function (x) {
        error(x.status === 413 || x.status === 0 ? "Las fotos pesan más de lo que acepta el servidor. Pruebe con menos o más livianas." : "Error de conexión. Intente de nuevo.");
      })
      .always(function () { $btn.prop("disabled", false).html(texto); });
  });

  /* 4. Botones de acción */
  $(document).on("click", "[data-pe-accion]", function (e) {
    e.preventDefault();
    var $b = $(this);
    var enviar = function () {
      $b.prop("disabled", true);
      $.post($b.data("pe-accion"), csrf())
        .done(function (r) {
          if (r && r.exito) {
            // dónde volver: la copia nueva; si se borró, la sección (o la anterior); si no, el mismo lugar
            var $sec = $b.closest("[data-pe-seccion]");
            var $bloque = $b.closest("[data-pe-bloque]");
            var ir;
            if (r.id) { ir = ($bloque.length ? "pe-bloque-" : "pe-sec-") + r.id; }
            else if (/eliminar/.test($b.data("pe-accion"))) { ir = $bloque.length ? $sec.attr("id") : ($sec.prev("[data-pe-seccion]").attr("id") || null); }
            else { ir = ($bloque.length ? $bloque : $sec).attr("id") || null; }
            recargar(ir);
          } else {
            error(r && r.mensaje);
            $b.prop("disabled", false);
          }
        })
        .fail(function () { error("Error de conexión. Intente de nuevo."); $b.prop("disabled", false); });
    };
    if (!$b.data("confirmar")) { enviar(); return; }
    Swal.fire({
      icon: "warning", titleText: $b.data("confirmar"), showCancelButton: true,
      confirmButtonText: "Eliminar", cancelButtonText: "Cancelar", confirmButtonColor: "#a33a2c"
    }).then(function (res) { if (res.isConfirmed) enviar(); });
  });

  /* 5. Filas de una lista */
  function renumerar($form) {
    // índices 0, 1, 2… en el orden en que se ven (también en los nombres de las fotos: archivos[fotos.N.imagen])
    $form.find("[data-pe-lista]").each(function () {
      $(this).find("[data-pe-filas] > [data-pe-fila]").each(function (i) {
        $(this).find("[name]").each(function () {
          this.name = this.name.replace(/\]\[(?:\d+|n\d+|__i__)\]\[/, "][" + i + "][").replace(/\.(?:\d+|n\d+|__i__)\./, "." + i + ".");
        });
      });
    });
  }
  function actualizarLista($lista) {
    var $filas = $lista.find("[data-pe-filas] > [data-pe-fila]");
    var max = parseInt($lista.data("max"), 10) || 99;
    $filas.each(function (i) {
      var $n = $(this).find("[data-pe-fila-num]");
      if ($n.length) $n.text($n.text().replace(/\s\d+$/, "") + " " + (i + 1));
    });
    $lista.find("[data-pe-fila-agregar]").prop("hidden", $filas.length >= max);
  }
  var contador = 0;
  $(document).on("click", "[data-pe-fila-agregar]", function () {
    var $lista = $(this).closest("[data-pe-lista]");
    var html = $lista.children("template[data-pe-plantilla]").html().replace(/__i__/g, "n" + (++contador));
    // "n1", "n2"…: ids únicos mientras se edita; los nombres se renumeran 0, 1, 2… al guardar
    var $fila = $(html).appendTo($lista.find("[data-pe-filas]").first());
    iniciarEditores($fila);
    actualizarLista($lista);
    $fila.addClass("is-nueva").find("input,textarea").filter(":visible").first().trigger("focus");
  });
  $(document).on("click", "[data-pe-fila-quitar]", function () {
    var $lista = $(this).closest("[data-pe-lista]");
    $(this).closest("[data-pe-fila]").remove();
    actualizarLista($lista);
  });
  $(document).on("click", "[data-pe-fila-mover]", function () {
    var $fila = $(this).closest("[data-pe-fila]");
    if (parseInt($(this).data("pe-fila-mover"), 10) < 0) { $fila.prev("[data-pe-fila]").before($fila); } else { $fila.next("[data-pe-fila]").after($fila); }
    actualizarLista($fila.closest("[data-pe-lista]"));
  });
  $("[data-pe-lista]").each(function () { actualizarLista($(this)); });
  $(document).on("submit", "form[data-pe-banner]", function () { renumerar($(this)); });

  /* 6. Foto: vista previa al elegirla y "Quitar" */
  $(document).on("change", "[data-pe-imagen-archivo]", function () {
    var $caja = $(this).closest("[data-pe-imagen]");
    var f = this.files && this.files[0];
    if (!f) return;
    if (f.size > 8 * 1048576) {
      error("«" + f.name + "» pesa " + (f.size / 1048576).toFixed(1) + " MB. El máximo es 8 MB.");
      this.value = "";
      return;
    }
    $caja.find("[data-pe-imagen-vista]").html($("<img alt>").attr("src", URL.createObjectURL(f)));
    $caja.find("[data-pe-imagen-quitar]").prop("hidden", false);
  });
  $(document).on("click", "[data-pe-imagen-quitar]", function () {
    var $caja = $(this).closest("[data-pe-imagen]");
    $caja.find("[data-pe-imagen-valor]").val("");
    $caja.find("[data-pe-imagen-archivo]").val("");
    $caja.find("[data-pe-imagen-vista]").html('<i class="fa fa-picture-o"></i>');
    $(this).prop("hidden", true);
  });

  /* 7. Editor de texto simple */
  function iniciarEditores($donde) {
    if (typeof window.tinymce === "undefined") return;
    $donde.find("textarea[data-pe-html]").each(function () {
      if (this.id && window.tinymce.get(this.id)) return;
      var base = $(this).data("tinymce-base");
      window.tinymce.init({
        target: this,
        license_key: "gpl",
        base_url: base,
        suffix: ".min",
        language: "es",
        language_url: base + "/langs/es.js",
        promotion: false,
        branding: false,
        menubar: false,
        statusbar: false,
        height: 300,
        plugins: "lists link autoresize",
        autoresize_bottom_margin: 20,
        max_height: 520,
        toolbar: "undo redo | blocks | bold italic | bullist numlist | link | removeformat",
        block_formats: "Párrafo=p; Subtítulo=h3; Subtítulo chico=h4",
        valid_elements: "p,br,strong/b,em/i,u,h3,h4,ul,ol,li,a[href|title|target]",
        convert_urls: false,
        paste_as_text: false,
        content_style: "body{font-family:Figtree,Segoe UI,Arial,sans-serif;font-size:15px;line-height:1.6;color:#1d1b17}",
        setup: function (ed) { ed.on("change", function () { ed.save(); }); }
      });
    });
  }
  function quitarEditores($donde) {
    if (typeof window.tinymce === "undefined") return;
    $donde.find("textarea[data-pe-html]").each(function () {
      var ed = this.id && window.tinymce.get(this.id);
      if (ed) ed.remove();
    });
  }
  // TinyMCE abre sus menús (enlace) fuera del modal: que el modal de Bootstrap no les robe el foco
  $(document).on("focusin", function (e) {
    if ($(e.target).closest(".tox-tinymce-aux, .tox-dialog, .moxman-window, .tam-assetmanager-root").length) e.stopImmediatePropagation();
  });

  /* 8. Plegar */
  var plegados = leerSesion(CLAVE_PLEGADOS, {});
  function plegar($caja, si) {
    $caja.toggleClass("is-plegado", si);
    $caja.find("> .box-header [data-pe-plegar], > .huk-pe-bloque-cab [data-pe-plegar]").attr("aria-expanded", si ? "false" : "true");
    if ($caja.attr("id")) {
      if (si) { plegados[$caja.attr("id")] = 1; } else { delete plegados[$caja.attr("id")]; }
      guardarSesion(CLAVE_PLEGADOS, plegados);
    }
  }
  $.each(plegados, function (id) { $("#" + id).addClass("is-plegado"); });
  $(document).on("click", "[data-pe-plegar]", function () {
    var $caja = $(this).closest("[data-pe-bloque],[data-pe-seccion]");
    plegar($caja, !$caja.hasClass("is-plegado"));
  });
  $(document).on("click", "[data-pe-plegar-todo]", function () {
    var $secs = $("[data-pe-seccion]");
    var plegar_ = $secs.filter(":not(.is-plegado)").length > 0;
    $secs.each(function () { plegar($(this), plegar_); });
    $(this).html(plegar_ ? '<i class="fa fa-expand"></i> Desplegar todo' : '<i class="fa fa-compress"></i> Plegar todo');
  });

  /* 9. Plantillas */
  $(document).on("submit", "form[data-pe-plantilla-form]", function (e) {
    e.preventDefault();
    var $f = $(this);
    var reemplaza = String($f.data("reemplaza")) === "1";
    var nombre = $f.find("select option:selected").text();
    Swal.fire({
      icon: reemplaza ? "warning" : "question",
      titleText: "¿Cargar la plantilla «" + nombre + "»?",
      text: reemplaza ? "Se borran las secciones que tiene ahora la página (con sus bloques y fotos) y se ponen las de la plantilla." : "Se agregan sus secciones con textos guía para completar.",
      showCancelButton: true,
      confirmButtonText: reemplaza ? "Reemplazar" : "Cargar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: reemplaza ? "#a33a2c" : "#bd944f"
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var datos = $.extend(csrf(), { idplantilla: $f.find("select").val(), reemplazar: reemplaza ? 1 : 0 });
      $.post($f.attr("action"), datos)
        .done(function (r) {
          if (r && r.exito) {
            Swal.fire({ icon: "success", titleText: r.mensaje }).then(function () { recargar(null); });
          } else { error(r && r.mensaje); }
        })
        .fail(function () { error("Error de conexión. Intente de nuevo."); });
    });
  });
  $(document).on("click", "[data-pe-guardar-plantilla]", function () {
    var $b = $(this);
    var eco = $b.data("ecosistema");
    Swal.fire({
      titleText: "Guardar como plantilla",
      html: "<p>Se copian las secciones y los bloques de esta página (con sus fotos) a una plantilla nueva.</p>"
        + (eco ? '<label class="huk-pe-swal-casilla"><input type="checkbox" id="pe-pl-eco" checked> Sugerirla para «' + $("<span>").text(eco).html() + "»</label>" : ""),
      input: "text",
      inputPlaceholder: "Nombre de la plantilla",
      inputAttributes: { maxlength: 100 },
      showCancelButton: true,
      confirmButtonText: "Guardar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f",
      inputValidator: function (v) { return $.trim(v) === "" ? "Escriba un nombre." : null; }
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var datos = $.extend(csrf(), { nombre: res.value, ecosistema: $("#pe-pl-eco").is(":checked") ? 1 : 0 });
      $.post($b.data("pe-guardar-plantilla"), datos)
        .done(function (r) { r && r.exito ? Swal.fire({ icon: "success", titleText: r.mensaje }) : error(r && r.mensaje); })
        .fail(function () { error("Error de conexión. Intente de nuevo."); });
    });
  });

  /* 11. Tabla ------------------------------------------------------------- */
  // Modelo: {columnas: [..], filas: [[..], ..]}. La grilla se dibuja de nuevo al agregar o quitar filas y columnas.
  function datosTabla($t) {
    var d;
    try { d = JSON.parse($t.find("[data-pe-tabla-valor]").val() || "{}"); } catch (e) { d = {}; }
    d.columnas = $.isArray(d.columnas) ? d.columnas : [];
    d.filas = $.isArray(d.filas) ? d.filas : [];
    if (!d.columnas.length) d.columnas = ["", ""];
    if (!d.filas.length) d.filas = [d.columnas.map(function () { return ""; })];
    return d;
  }
  function leerGrilla($t) {
    var d = { columnas: [], filas: [] };
    $t.find("[data-pe-tabla-grilla] thead input").each(function () { d.columnas.push(this.value); });
    $t.find("[data-pe-tabla-grilla] tbody tr").each(function () {
      d.filas.push($(this).find("input").map(function () { return this.value; }).get());
    });
    return d;
  }
  function guardarTabla($t) {
    var d = leerGrilla($t);
    // las filas totalmente vacías no se guardan
    d.filas = d.filas.filter(function (f) { return f.join("").trim() !== ""; });
    $t.find("[data-pe-tabla-valor]").val(JSON.stringify(d));
  }
  function dibujarTabla($t, d) {
    var max = parseInt($t.data("max-celda"), 10) || 300;
    var celda = function (v, ph) { return $('<input type="text" class="form-control input-sm">').attr({ maxlength: max, placeholder: ph || "" }).val(v); };
    var $g = $t.find("[data-pe-tabla-grilla]").empty();
    var $cab = $("<tr>");
    d.columnas.forEach(function (c, i) {
      $("<th>").append(celda(c, "Título " + (i + 1)))
        .append($('<button type="button" class="huk-pe-tabla-x" data-pe-tabla-quitar-col title="Quitar columna">&times;</button>').attr("data-i", i))
        .appendTo($cab);
    });
    $cab.append("<th class=\"huk-pe-tabla-acc\"></th>");
    $("<thead>").append($cab).appendTo($g);
    var $cuerpo = $("<tbody>");
    d.filas.forEach(function (f) {
      var $tr = $("<tr>");
      d.columnas.forEach(function (c, i) { $("<td>").append(celda(f[i] || "")).appendTo($tr); });
      $tr.append('<td class="huk-pe-tabla-acc"><button type="button" class="huk-pe-tabla-x" data-pe-tabla-quitar-fila title="Quitar fila">&times;</button></td>');
      $cuerpo.append($tr);
    });
    $g.append($cuerpo);
    $t.find("[data-pe-tabla-columna]").prop("disabled", d.columnas.length >= (parseInt($t.data("max-columnas"), 10) || 8));
    $t.find("[data-pe-tabla-fila]").prop("disabled", d.filas.length >= (parseInt($t.data("max-filas"), 10) || 80));
  }
  function iniciarTablas($donde) {
    $donde.find("[data-pe-tabla]").each(function () { dibujarTabla($(this), datosTabla($(this))); });
  }
  $(document).on("click", "[data-pe-tabla-columna]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var d = leerGrilla($t);
    d.columnas.push("");
    d.filas.forEach(function (f) { f.push(""); });
    dibujarTabla($t, d);
    $t.find("thead input").last().trigger("focus");
  });
  $(document).on("click", "[data-pe-tabla-fila]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var d = leerGrilla($t);
    d.filas.push(d.columnas.map(function () { return ""; }));
    dibujarTabla($t, d);
    $t.find("tbody tr").last().find("input").first().trigger("focus");
  });
  $(document).on("click", "[data-pe-tabla-quitar-col]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var d = leerGrilla($t);
    if (d.columnas.length <= 1) return;
    var i = parseInt($(this).attr("data-i"), 10);
    d.columnas.splice(i, 1);
    d.filas.forEach(function (f) { f.splice(i, 1); });
    dibujarTabla($t, d);
  });
  $(document).on("click", "[data-pe-tabla-quitar-fila]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var $tr = $(this).closest("tr");
    if ($tr.siblings().length) { $tr.remove(); } else { $tr.find("input").val(""); }
    dibujarTabla($t, leerGrilla($t));
  });
  // Enter baja a la celda de abajo (y crea la fila si es la última)
  $(document).on("keydown", "[data-pe-tabla-grilla] input", function (e) {
    if (e.key !== "Enter") return;
    e.preventDefault();
    var $td = $(this).parent();
    var col = $td.index();
    var $sig = $td.closest("tr").next("tr");
    if (!$sig.length && $td.closest("thead").length) $sig = $td.closest("table").find("tbody tr").first();
    if (!$sig.length) { $(this).closest("[data-pe-tabla]").find("[data-pe-tabla-fila]").trigger("click"); return; }
    $sig.children().eq(col).find("input").trigger("focus");
  });
  // Negrita y chips: envuelven lo seleccionado en la última celda usada
  var ultimaCelda = null;
  $(document).on("focus", "[data-pe-tabla-grilla] input", function () { ultimaCelda = this; });
  $(document).on("mousedown", "[data-pe-tabla-marca]", function (e) { e.preventDefault(); }); // no perder la selección
  $(document).on("click", "[data-pe-tabla-marca]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var el = ultimaCelda && $.contains($t[0], ultimaCelda) ? ultimaCelda : null;
    if (!el) { error("Primero haga clic en una celda (y si desea, seleccione el texto)."); return; }
    var p = String($(this).data("pe-tabla-marca")).split("|");
    var a = el.selectionStart, b = el.selectionEnd, v = el.value;
    var sel = v.slice(a, b) || (a === b && v ? "" : "texto");
    if (a === b && v) { a = 0; b = v.length; sel = v; } // sin selección: toda la celda
    el.value = v.slice(0, a) + p[0] + sel + p[1] + v.slice(b);
    el.focus();
    el.setSelectionRange(a + p[0].length, a + p[0].length + sel.length);
  });
  // Pegar desde Excel / Google Sheets / Word: texto separado por tabulaciones (la primera fila son los títulos)
  function tablaDesdeTexto(texto) {
    var lineas = texto.replace(/\r/g, "").replace(/\n+$/, "").split("\n");
    var sep = lineas.some(function (l) { return l.indexOf("\t") > -1; }) ? "\t" : (lineas[0].indexOf(";") > -1 ? ";" : ",");
    var filas = lineas.map(function (l) { return l.split(sep).map(function (c) { return c.trim().replace(/^"(.*)"$/, "$1"); }); });
    var n = Math.max.apply(null, filas.map(function (f) { return f.length; }));
    filas = filas.map(function (f) { while (f.length < n) f.push(""); return f; });
    return { columnas: filas.shift(), filas: filas };
  }
  $(document).on("click", "[data-pe-tabla-pegar]", function () {
    var $t = $(this).closest("[data-pe-tabla]");
    var maxC = parseInt($t.data("max-columnas"), 10) || 8, maxF = parseInt($t.data("max-filas"), 10) || 80;
    Swal.fire({
      titleText: "Pegar desde Excel",
      html: "<p class=\"huk-pe-swal-ayuda\">En Excel (o Google Sheets) seleccione las celdas, cópielas con <b>Ctrl+C</b> y péguelas aquí con <b>Ctrl+V</b>. La primera fila son los títulos de las columnas. Reemplaza lo que tiene la tabla.</p>",
      input: "textarea",
      inputAttributes: { rows: 8, spellcheck: "false" },
      customClass: { input: "huk-pe-swal-pegar" },
      showCancelButton: true,
      confirmButtonText: "Usar estos datos",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#bd944f",
      inputValidator: function (v) {
        if (!$.trim(v)) return "Pegue las celdas copiadas.";
        var d = tablaDesdeTexto(v);
        if (d.columnas.length > maxC) return "Tiene " + d.columnas.length + " columnas. El máximo es " + maxC + ".";
        if (d.filas.length > maxF) return "Tiene " + d.filas.length + " filas. El máximo es " + maxF + ".";
        return null;
      }
    }).then(function (res) {
      if (!res.isConfirmed) return;
      var d = tablaDesdeTexto(res.value);
      if (!d.filas.length) d.filas = [d.columnas.map(function () { return ""; })];
      dibujarTabla($t, d);
    });
  });
  // Pegar varias celdas directamente sobre una celda de la grilla: se reparten hacia la derecha y abajo
  $(document).on("paste", "[data-pe-tabla-grilla] input", function (e) {
    var texto = (e.originalEvent.clipboardData || window.clipboardData).getData("text");
    if (texto.indexOf("\t") < 0 && texto.replace(/\r?\n$/, "").indexOf("\n") < 0) return; // una sola celda: pegado normal
    e.preventDefault();
    var $t = $(this).closest("[data-pe-tabla]");
    var enCabecera = $(this).closest("thead").length > 0;
    var f0 = enCabecera ? -1 : $(this).closest("tr").index();
    var c0 = $(this).parent().index();
    var d = leerGrilla($t);
    var maxC = parseInt($t.data("max-columnas"), 10) || 8, maxF = parseInt($t.data("max-filas"), 10) || 80;
    texto.replace(/\r/g, "").replace(/\n+$/, "").split("\n").forEach(function (l, i) {
      l.split("\t").forEach(function (v, j) {
        var c = c0 + j, f = f0 + i;
        if (c >= maxC || f >= maxF) return;
        while (d.columnas.length <= c) { d.columnas.push(""); d.filas.forEach(function (x) { x.push(""); }); }
        if (f < 0) { d.columnas[c] = v.trim(); return; }
        while (d.filas.length <= f) d.filas.push(d.columnas.map(function () { return ""; }));
        d.filas[f][c] = v.trim();
      });
    });
    dibujarTabla($t, d);
  });

  /* 12. Subir varias fotos: una fila nueva por foto ------------------------ */
  var MAX_FOTO = 8 * 1048576;
  $(document).on("change", "[data-pe-varias-archivos]", function () {
    var $lista = $(this).closest("[data-pe-lista]");
    var archivos = Array.prototype.slice.call(this.files || []);
    this.value = "";
    if (!archivos.length) return;
    var max = parseInt($lista.data("max"), 10) || 99;
    // primero se llenan las filas que no tienen foto; después se agregan filas
    var libres = $lista.find("[data-pe-filas] > [data-pe-fila]").filter(function () {
      var $img = $(this).find("[data-pe-imagen]").first();
      return !$img.find("[data-pe-imagen-valor]").val() && !($img.find("[data-pe-imagen-archivo]")[0].files || []).length;
    }).get();
    var avisos = [];
    var grandes = archivos.filter(function (f) { return f.size > MAX_FOTO; });
    archivos = archivos.filter(function (f) { return f.size <= MAX_FOTO && /^image\/(jpeg|png|webp)$/.test(f.type); });
    if (grandes.length) avisos.push(grandes.length + " foto(s) pesan más de 8 MB y no se agregaron.");
    var cuantas = $lista.find("[data-pe-filas] > [data-pe-fila]").length;
    var lugar = libres.length + Math.max(0, max - cuantas);
    if (archivos.length > lugar) {
      avisos.push("Entran " + lugar + " foto(s) más (máximo " + max + "). Se agregaron las primeras.");
      archivos = archivos.slice(0, lugar);
    }
    archivos.forEach(function (f) {
      var fila = libres.shift();
      if (!fila) {
        $lista.find("[data-pe-fila-agregar]").first().trigger("click");
        fila = $lista.find("[data-pe-filas] > [data-pe-fila]").last()[0];
      }
      var input = $(fila).find("[data-pe-imagen-archivo]")[0];
      try {
        var dt = new DataTransfer();
        dt.items.add(f);
        input.files = dt.files;
        $(input).trigger("change");
      } catch (e) { avisos.push("Este navegador no deja cargar varias a la vez: subilas de a una."); }
    });
    // lo que acepta el servidor por envío (post_max_size)
    var total = 0;
    $lista.closest("form").find("input[type=file]").each(function () { $.each(this.files || [], function (i, f) { total += f.size; }); });
    var limite = parseInt($lista.closest("form").data("post-max"), 10) || 0;
    if (limite && total > limite * 0.95) {
      avisos.push("Entre todas pesan " + (total / 1048576).toFixed(1) + " MB y el servidor acepta hasta " + Math.floor(limite / 1048576) + " MB por vez. Guarde con menos fotos y agregue el resto después.");
    }
    if (avisos.length) Swal.fire({ icon: "warning", html: avisos.map(function (m) { return $("<p>").text(m).prop("outerHTML"); }).join("") });
  });

  /* 13. Elegir fotos, videos o documentos ---------------------------------- */
  function contarElegidos($caja) {
    var n = $caja.find("[data-pe-elegir] input:checked").length;
    var total = $caja.find("[data-pe-elegir] input").length;
    $caja.find("[data-pe-elegir-cuenta]").text(n + " de " + total + " marcados");
  }
  $(document).on("change", "[data-pe-elegir] input", function () { contarElegidos($(this).closest(".huk-pe-elegir")); });
  $(document).on("click", "[data-pe-elegir-todo]", function () {
    var $caja = $(this).closest(".huk-pe-elegir");
    $caja.find("[data-pe-elegir] input").prop("checked", String($(this).data("pe-elegir-todo")) === "1");
    contarElegidos($caja);
  });

  /* 14. Campos condicionales ------------------------------------------------ */
  function valorCampo($form, campo) {
    var $i = $form.find('[name="Bloque[' + campo + ']"]').filter("[type=radio],[type=checkbox]");
    var $m = $i.filter(":checked");
    return $m.length ? String($m.last().val()) : ($i.length ? "0" : "");
  }
  function actualizarSi($donde) {
    $donde.find("[data-pe-si]").each(function () {
      var p = String($(this).data("pe-si")).split(":");
      var ok = p[1].split(",").indexOf(valorCampo($(this).closest("form"), p[0])) > -1;
      $(this).prop("hidden", !ok);
    });
  }
  $(document).on("change", "form[data-pe-form] input[type=radio], form[data-pe-form] input[type=checkbox]", function () {
    actualizarSi($(this).closest("form"));
  });

})(jQuery);
