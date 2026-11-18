/* ==========================================================================
   HUK · Campo de una imagen (app\widgets\CampoImagen)
   <div data-huk-imagen data-actual="1|0">
     input file (Kartik FileInput) · <input type=hidden data-huk-imagen-quitado>
     <button data-huk-imagen-elegir> · <button data-huk-imagen-quitar>
   "Quitar" saca lo que se ve: primero la imagen recién elegida; si no hay, la guardada
   (se marca para borrar al guardar; Cancelar no borra nada).
   ========================================================================== */
(function ($) {
  "use strict";

  function partes(el) {
    var $w = $(el).closest("[data-huk-imagen]");
    return { $w: $w, $in: $w.find("input[type=file]"), $quitado: $w.find("[data-huk-imagen-quitado]") };
  }

  function hayNueva(p) {
    return !!(p.$in[0] && p.$in[0].files && p.$in[0].files.length);
  }

  function hayGuardada(p) {
    return String(p.$w.data("actual")) === "1" && p.$quitado.val() !== "1";
  }

  function actualizar(el) {
    var p = partes(el);
    var tiene = hayNueva(p) || hayGuardada(p);
    p.$w.toggleClass("tiene-imagen", tiene);
    p.$w.find("[data-huk-imagen-elegir] span").text(tiene ? "Cambiar imagen" : "Elegir imagen");
  }

  $(document).on("click", "[data-huk-imagen-elegir]", function () {
    // Con showBrowse=false el plugin bloquea los clics al input salvo que vengan de su zona
    // (marca "zoneClicked"); usamos la misma marca para abrir el selector de archivos.
    partes(this).$in.data("zoneClicked", true).trigger("click");
  });

  /* Vuelve a armar el plugin mostrando (o no) la imagen guardada. Copia simple de opciones:
     así las listas se reemplazan y no se mezclan con las anteriores. */
  function reiniciar(p, conGuardada) {
    var plugin = p.$in.data("fileinput");
    if (!plugin) return;
    var opciones = $.extend({}, plugin.options, {
      initialPreview: conGuardada ? [p.$w.data("url")] : [],
      initialPreviewConfig: conGuardada ? [{ caption: "Imagen guardada", showDelete: false, showDrag: false }] : []
    });
    p.$in.fileinput("destroy");
    p.$in.val("");
    p.$in.fileinput(opciones);
  }

  $(document).on("click", "[data-huk-imagen-quitar]", function () {
    var p = partes(this);
    if (hayNueva(p)) {
      reiniciar(p, hayGuardada(p)); // saca la recién elegida; vuelve la guardada si había
    } else if (hayGuardada(p)) {
      p.$quitado.val("1");
      reiniciar(p, false);
    }
    actualizar(this);
  });

  $(document).on("change fileselect filecleared fileclear filebatchselected filereset", "[data-huk-imagen] input[type=file]", function () {
    var el = this;
    setTimeout(function () { actualizar(el); }, 0); // después de que el plugin termine
  });

  $(function () {
    $("[data-huk-imagen]").each(function () { actualizar(this); });
  });
})(jQuery);
