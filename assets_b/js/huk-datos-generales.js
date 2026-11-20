/* HUK · Datos generales: vista previa del mapa.
   El textarea [data-mapa-input] acepta el HTML de "Insertar un mapa" o el enlace.
   Solo se muestra si es un enlace https://www.google.com/maps/embed?pb=... (lo mismo valida el servidor). */
(function ($) {
  "use strict";

  var PATRON = /^https:\/\/www\.google\.com\/maps\/embed\?pb=[A-Za-z0-9!._%\-:+,*~]+$/;

  function extraer(texto) {
    texto = $.trim(texto || "");
    var m = texto.match(/\bsrc\s*=\s*["']([^"']+)["']/i);
    if (m) { texto = m[1]; }
    texto = $("<textarea>").html(texto).text(); // decodifica &amp; etc.
    return PATRON.test(texto) ? texto : null;
  }

  function actualizar($input) {
    var $caja = $("[data-mapa-preview]");
    var $marco = $caja.find("iframe");
    var $aviso = $caja.find("[data-mapa-aviso]");
    var texto = $input.val();
    var enlace = extraer(texto);

    if (enlace) {
      if ($marco.attr("src") !== enlace) { $marco.attr("src", enlace); }
      $marco.prop("hidden", false);
      $aviso.prop("hidden", true);
    } else {
      $marco.prop("hidden", true).removeAttr("src");
      $aviso.prop("hidden", false).text($.trim(texto) === ""
        ? "Todavía no hay mapa."
        : "Así no se puede mostrar: copie el HTML de Compartir → Insertar un mapa.");
    }
  }

  $(function () {
    var $input = $("[data-mapa-input]");
    if (!$input.length) { return; }
    actualizar($input);
    $input.on("input change", function () { actualizar($input); });
  });
})(jQuery);
