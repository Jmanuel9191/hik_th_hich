/* ==========================================================================
   HUK · Configuración de GTranslate
   Los idiomas se leen del selector del header (_header.php, atributos data-lang),
   así la lista existe en un solo lugar: el arreglo $idiomas de PHP.
   ========================================================================== */
(function () {
  "use strict";
  var codigos = Array.prototype.map.call(
    document.querySelectorAll("[data-lang-switch] [data-lang]"),
    function (li) { return li.getAttribute("data-lang"); }
  );
  window.gtranslateSettings = {
    default_language: "es",
    languages: codigos.length ? codigos : ["es"],
    wrapper_selector: ".gtranslate_wrapper"
  };
})();
