/* ==========================================================================
   HUK · Solicitud de inversor (web y panel)
   - Persona / Empresa: <input data-inversor-tipo> muestra u oculta [data-solo-empresa]
     y cambia la etiqueta de [data-etiqueta-persona] / [data-etiqueta-empresa].
   - Ver la contraseña: <button data-ver-clave="#id">.
   - Documento [data-archivo]: arrastrar o elegir; vista previa (foto: miniatura · PDF: ícono), nombre y peso,
     "Cambiar" y "Quitar". Revisa formato y tamaño antes de enviar (el servidor lo vuelve a revisar).
   - Moneda [data-moneda]: el código elegido (USD, BOB…) se ve adelante de los montos [data-monto-moneda].
   - Términos [data-aceptar-terminos="#casilla"]: "Leí y acepto" en el modal marca la casilla.
   - Errores al enviar [data-errores-registro]: la página baja sola al resumen; cada error lleva a su campo.
   ========================================================================== */
(function ($) {
  "use strict";

  function aplicar() {
    var $marcado = $("[data-inversor-tipo]:checked");
    if (!$marcado.length) return;
    var tipo = $marcado.val();
    $("[data-solo-empresa]").toggleClass("d-none hidden", tipo !== "empresa");
    $("[data-etiqueta-" + tipo + "]").each(function () {
      var $l = $(this);
      var $req = $l.find(".required, abbr").detach();
      $l.text($l.data("etiqueta-" + tipo)).append($req);
    });
    $("[data-inversor-tipo]").closest("label").removeClass("is-activo");
    $marcado.closest("label").addClass("is-activo");
  }
  $(document).on("change", "[data-inversor-tipo]", aplicar);
  $(aplicar);

  $(document).on("click", "[data-ver-clave]", function (e) {
    e.preventDefault();
    var $i = $($(this).data("ver-clave"));
    var ver = $i.attr("type") === "password";
    $i.attr("type", ver ? "text" : "password");
    $(this).find("i").toggleClass("fa-eye fa-eye-slash");
    $(this).attr("aria-label", ver ? "Ocultar contraseña" : "Ver contraseña");
  });
  /* Documento con vista previa ------------------------------------------- */
  var TIPOS = /^(image\/(jpeg|png|webp)|application\/pdf)$/;
  function peso(b) { return b >= 1048576 ? (b / 1048576).toFixed(1).replace(".", ",") + " MB" : Math.max(1, Math.round(b / 1024)) + " KB"; }
  function errorArchivo($c, texto) {
    var $g = $c.closest(".mb-3");
    $g.find(".invalid-feedback").text(texto || "");
    $g.toggleClass("has-error", !!texto);
  }
  function mostrar($c, f) {
    var $mini = $c.find("[data-archivo-mini]").empty();
    if (/^image\//.test(f.type)) {
      var url = URL.createObjectURL(f);
      $("<img alt=''>").attr("src", url).on("load", function () { URL.revokeObjectURL(url); }).appendTo($mini);
    } else {
      $mini.append('<i class="fa-solid fa-file-pdf" aria-hidden="true"></i>');
    }
    $c.find("[data-archivo-nombre]").text(f.name);
    $c.find("[data-archivo-peso]").text((/pdf/.test(f.type) ? "PDF" : "Foto") + " · " + peso(f.size));
    $c.find("[data-archivo-zona]").prop("hidden", true);
    $c.find("[data-archivo-vista]").prop("hidden", false);
    $c.addClass("is-cargado");
  }
  function limpiar($c) {
    $c.find("input[type=file]").val("");
    $c.find("[data-archivo-vista]").prop("hidden", true);
    $c.find("[data-archivo-zona]").prop("hidden", false);
    $c.removeClass("is-cargado");
  }
  $(document).on("change", "[data-archivo] input[type=file]", function () {
    var $c = $(this).closest("[data-archivo]");
    var f = this.files && this.files[0];
    if (!f) { limpiar($c); return; }
    var max = (parseInt($c.data("max-mb"), 10) || 8) * 1048576;
    if (!TIPOS.test(f.type)) { limpiar($c); errorArchivo($c, "Ese archivo no sirve: suba una foto (JPG, PNG o WEBP) o un PDF."); return; }
    if (f.size > max) { limpiar($c); errorArchivo($c, "El archivo pesa " + peso(f.size) + ". El máximo es " + $c.data("max-mb") + " MB."); return; }
    errorArchivo($c, "");
    mostrar($c, f);
  });
  $(document).on("click", "[data-archivo-quitar]", function () { limpiar($(this).closest("[data-archivo]")); });
  $(document).on("dragenter dragover", "[data-archivo-zona]", function (e) { e.preventDefault(); $(this).addClass("is-encima"); });
  $(document).on("dragleave drop", "[data-archivo-zona]", function (e) { e.preventDefault(); $(this).removeClass("is-encima"); });
  $(document).on("drop", "[data-archivo-zona]", function (e) {
    var archivos = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
    if (!archivos || !archivos.length) return;
    var input = $(this).closest("[data-archivo]").find("input[type=file]")[0];
    try { input.files = archivos; } catch (err) { return; }
    $(input).trigger("change");
  });

  /* Moneda de los montos ---------------------------------------------------- */
  function monedaElegida($sel) {
    var codigos = $sel.data("codigos") || {};
    $("[data-monto-moneda]").text(codigos[$sel.val()] || "");
  }
  $(document).on("change", "[data-moneda]", function () { monedaElegida($(this)); });
  $(function () { $("[data-moneda]").each(function () { monedaElegida($(this)); }); });

  /* Términos: "Leí y acepto" marca la casilla ------------------------------- */
  $(document).on("click", "[data-aceptar-terminos]", function () {
    $($(this).data("aceptar-terminos")).prop("checked", true).trigger("change");
  });

  /* Errores después de enviar: bajar al resumen y que se note ------------- */
  $(function () {
    var $e = $("[data-errores-registro]");
    if (!$e.length) return;
    var quieto = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var top = $e.offset().top - 130; // debajo del menú fijo
    window.scrollTo({ top: top, behavior: quieto ? "auto" : "smooth" });
    setTimeout(function () { $e.trigger("focus").addClass("is-atencion"); }, quieto ? 0 : 450);
  });
  $(document).on("click", "[data-ir-campo]", function (e) {
    var $c = $($(this).attr("href"));
    if (!$c.length) return;
    e.preventDefault();
    var $grupo = $c.closest(".mb-3");
    window.scrollTo({ top: $grupo.offset().top - 140, behavior: "smooth" });
    setTimeout(function () { $c.trigger("focus"); $grupo.addClass("is-atencion"); setTimeout(function () { $grupo.removeClass("is-atencion"); }, 1600); }, 400);
  });

  /* Al enviar: el botón queda "Enviando…" para que no se mande dos veces */
  $(document).on("beforeSubmit", "#registro-form", function () {
    $(this).find("[data-enviar-solicitud]").prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Enviando…');
    return true;
  });
})(jQuery);
